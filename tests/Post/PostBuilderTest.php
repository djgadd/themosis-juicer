<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Post\Model;
use Com\KeltieCochrane\Juicer\Post\Builder;

/**
 * @group  Post
 */
class PostBuilderTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var \Com\KeltieCochrane\Juicer\Post\Builder
   */
  protected $builder;

  /**
   * Create a builder for us to use
   * @return void
  **/
  public function setUp()
  {
    $this->builder = new Builder('keltie-cochrane');
  }

  /**
   * Test ->filter()
   * @return void
   */
  public function testFilter ()
  {
    $this->assertEmpty($this->builder->getFilters());

    // Ensure we can add a filter
    $this->builder->filter('facebook');
    $this->assertContains('facebook', $this->builder->getFilters());

    // Ensure filters are trimmed
    $this->builder->filter('twitter ');
    $this->assertContains('twitter', $this->builder->getFilters());

    // Ensure we have 2 filters
    $this->assertCount(2, $this->builder->getFilters());

    // Ensure we get a builder back
    $builder = $this->builder->filter('instagram');
    $this->assertInstanceOf(Builder::class, $builder);
  }

  /**
   * Test ->from()
   * @return void
   */
  public function testFrom ()
  {
    $date = Carbon::now();
    $this->builder->from($date);

    $query = $this->builder->getQuery();
    $this->assertArrayHasKey('starting_at', $query);
    $this->assertEquals($query['starting_at'], $date->format('Y-m-d H:i'));
  }

  /**
   * Test ->to()
   * @return void
   */
  public function testTo ()
  {
    $date = Carbon::now();
    $this->builder->to($date);

    $query = $this->builder->getQuery();
    $this->assertArrayHasKey('ending_at', $query);
    $this->assertEquals($query['ending_at'], $date->format('Y-m-d H:i'));
  }

  /**
   * Tests that a ->to() date before a ->from() date will throw an Exception
   * @return void
   * @expectedException Com\KeltieCochrane\Juicer\Exceptions\InvalidDateRangeException
   */
  public function testInvalidFromToThrowsInvalidDateRangeException ()
  {
    $from = Carbon::now();
    $to = (clone $from)->subDay(1);

    $this->builder->from($from);
    $this->builder->to($to);
  }

  /**
   * Tests that a ->from() date after a ->to() date will throw an Exception
   * @return void
   * @expectedException Com\KeltieCochrane\Juicer\Exceptions\InvalidDateRangeException
   */
  public function testInvalidToFromThrowsInvalidDateRangeException ()
  {
    $to = Carbon::now();
    $from = (clone $to)->addDay(1);

    $this->builder->to($to);
    $this->builder->from($from);
  }

  /**
   * Tests ->get()
   * @return void
   */
  public function testGet ()
  {
    // Mock Guzzle
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $this->builder = new Builder('keltie-cochrane', $client);

    // Get our mocked posts
    $posts = $this->builder->get(true);

    // Verify we made a request
    $this->assertCount(1, $transactions);
    $transaction = $transactions[0];
    $this->assertEquals('GET', $transaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
    $this->assertEquals('/api/feeds/keltie-cochrane', $transaction['request']->getUri()->getPath());

    // Validate that we got a Collection of Models back
    $this->assertInstanceOf(Collection::class, $posts);
    $this->assertInstanceOf(Model::class, $posts->first());

    // Validate that we can access properties
    $this->assertEquals(95568016, $posts->first()->id);
    $this->assertEquals(64592, $posts->first()->source->id);
  }

  /**
   * Tests ->paginate()
   * @return void
   */
  public function testPaginate ()
  {
    // Mock Guzzle
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $this->builder = new Builder('keltie-cochrane', $client);

    // Paginate
    $this->builder->paginate(1, 10, true);

    // Check the query
    $query = $this->builder->getQuery();
    $this->arrayHasKey('per', $query);
    $this->arrayHasKey('page', $query);
    $this->assertEquals(1, $query['page']);
    $this->assertEquals(10, $query['per']);
  }

  /**
   * Tests ->get() local caching
   * @return  void
   */
  public function testGetLocalCache ()
  {
    // Mock Guzzle
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $this->builder = new Builder('keltie-cochrane', $client);

    // Get our mocked posts (force cache)
    $this->builder->get(true);

    // Get our local cache
    $posts = $this->builder->get(false);

    // Check we only made 1 request
    $this->assertCount(1, $transactions);

    // Validate that we got a Collection of Models back
    $this->assertInstanceOf(Collection::class, $posts);
    $this->assertInstanceOf(Model::class, $posts->first());

    // Validate that we can access properties
    $this->assertEquals(95568016, $posts->first()->id);
    $this->assertEquals(64592, $posts->first()->source->id);
  }

  /**
   * Tests that changing the query invalidates the local cache
   * @return  void
   */
  public function testLocalCacheInvalidated ()
  {
    // Mock Guzzle
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $this->builder = new Builder('keltie-cochrane', $client);
    $posts = get_class_property(Builder::class, 'posts');

    // Get our mocked posts (force cache)
    $this->builder->get(true);

    // Ensure we made a request and locally cached the posts
    $this->assertCount(1, $transactions);
    $this->assertNotEmpty($posts->getValue($this->builder));

    // Change the builder and make sure it emptied it's own cache
    $this->builder->filter('Twitter');
    $this->assertEmpty($posts->getValue($this->builder));
  }

  /**
   * Tests ->get() persisted caching
   * @return  void
   */
  public function testGetPersistedCache ()
  {
    // Mock Guzzle
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $this->builder = new Builder('keltie-cochrane', $client);

    // Get our mocked posts (force cache)
    $this->builder->get(true);

    // Ensure we made a request and locally cached the posts
    $this->assertCount(1, $transactions);

    // Change the builder to invalidate the internal cache
    $this->builder->filter('Twitter');

    // Get posts from the cache
    $posts = $this->builder->get();

    // Verify we got posts
    $this->assertFalse($posts->isEmpty());
  }
}
