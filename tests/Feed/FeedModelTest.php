<?php

use Com\KeltieCochrane\Juicer\Feed\Model;
use Com\KeltieCochrane\Juicer\Post\Builder as PostBuilder;
use Com\KeltieCochrane\Juicer\Source\Builder as SourceBuilder;

/**
 * @group  Feed
 */
class FeedModelTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var  \Com\KeltieCochrane\Juicer\Feed\Model
   */
  protected $model;

  /**
   * Sets up a builder for us to test
   * @return  void
   */
  public function setUp ()
  {
    $this->model = new Model('keltie-cochrane');
  }

  /**
   * Tests ->save()
   * @return  void
   */
  public function testSave ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json', 'feed-get-success.json'], $transactions);

    $this->assertFalse($this->model->save($client));

    // Change the name
    $this->model->name = 'Test Name';
    $this->assertTrue($this->model->save($client));

    // If we ask it to save again it shouldn't
    $this->assertFalse($this->model->save($client));

    // Check transactions
    $this->assertCount(2, $transactions);

    // Check load transaction
    $loadTransaction = $transactions[0];
    $this->assertEquals('GET', $loadTransaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $loadTransaction['request']->getUri()->getHost());
    $this->assertEquals('/api/feeds/keltie-cochrane', $loadTransaction['request']->getUri()->getPath());

    // Check save transaction
    $saveTransaction = $transactions[1];
    $this->assertEquals('PUT', $saveTransaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $saveTransaction['request']->getUri()->getHost());
    $this->assertEquals('/api/feeds/keltie-cochrane', $saveTransaction['request']->getUri()->getPath());
  }

  /**
   * Tests ->save() with an invalid frequency throws InvalidFrequencyException
   * @return void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\InvalidFrequencyException
   */
  public function testSaveWithInvalidFrequencyThrowsInvalidFrequencyException ()
  {
    try {
      $transactions = [];
      $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
      $this->model->update_frequency = 'invalid';
      $this->model->save($client);
    }
    catch (\Com\KeltieCochrane\Juicer\Exceptions\InvalidFrequencyException $e) {
      // Check transactions
      $this->assertCount(1, $transactions);
      $transaction = $transactions[0];

      $this->assertEquals('GET', $transaction['request']->getMethod());
      $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
      $this->assertEquals('/api/feeds/keltie-cochrane', $transaction['request']->getUri()->getPath());

      // Throw the Exception to ensure we pass the test
      throw $e;
    }
  }

  /**
   * Tests ->load()
   * @return void
   */
  public function testLoad ()
  {
    $transactions = [];
    $load = get_class_method(Model::class, 'load');
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $model = $load->invokeArgs($this->model, [true, $client]);

    $this->assertInstanceOf(Model::class, $model);
    $this->assertEquals('Keltie Cochrane', $model->name);

    // Check transactions
    $this->assertCount(1, $transactions);

    $transaction = $transactions[0];
    $this->assertEquals('GET', $transaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
    $this->assertEquals('/api/feeds/keltie-cochrane', $transaction['request']->getUri()->getPath());
  }

  /**
   * Tests ->posts()
   * @return  void
   */
  public function testsPosts ()
  {
    $this->assertInstanceOf(PostBuilder::class, $this->model->posts());
  }

  /**
   * Tests ->deletePost()
   * @return  void
   */
  public function testDeletePost ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);

    $this->assertTrue($this->model->deletePost(55502267, $client));
    $this->assertCount(1, $transactions);
  }

  /**
   * Tests ->sources()
   * @return  void
   */
  public function testSources ()
  {
    $this->assertInstanceOf(SourceBuilder::class, $this->model->sources());
  }

  /**
   * Tests ->createSource()
   * @return  void
   */
  public function testCreateSource ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json', 'feed-get-success.json'], $transactions);
    $model = $this->model->createSource('twitter', 'KELTIECOCHRANE', 'username', $client);

    // Ensure we made a request
    $this->assertCount(2, $transactions);
  }

  /**
   * Tests ->deleteSource()
   * @return  void
   */
  public function testDeleteSource ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);

    $this->assertTrue($this->model->deleteSource(64592, $client));
    $this->assertCount(1, $transactions);
  }
}
