<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Post\Model;

class PostModelTest extends PHPUnit_Framework_TestCase
{
  public function testNewWithoutLoad ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['post-get-success.json'], $transactions);
    $model = new Model(55502267, false, $client);

    // Ensure it didn't load anything
    $this->assertCount(0, $transactions);
  }

  /**
   * Tests __construct when asking the model to load itself
   * @return void
   */
  public function testNewWithLoad ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['post-get-success.json'], $transactions);
    $model = new Model(55502267, true, $client);

    $this->assertCount(1, $transactions);

    $transaction = $transactions[0];
    $this->assertEquals('GET', $transaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
    $this->assertEquals('/api/posts/55502267', $transaction['request']->getUri()->getPath());

    $this->assertEquals(55502267, $model->id);
    $this->assertEquals('Keltie Cochrane', $model->poster_name);
  }

  /**
   * Tests ->load() when a Model doesn't have an ID
   * @return void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelLoadException
   */
  public function testNewWithLoadAnonymous ()
  {
    $model = new Model(null, true);
  }

  /**
   * Tests ->delete()
   * @return void
   */
  public function testdelete ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['post-get-success.json'], $transactions);
    $model = new Model(55502267, false, $client);

    $this->assertTrue($model->delete($client));

    $this->assertCount(1, $transactions);

    $transaction = $transactions[0];
    $this->assertEquals('DELETE', $transaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
    $this->assertEquals('/api/posts/55502267', $transaction['request']->getUri()->getPath());

    parse_str((string) $transaction['request']->getBody(), $params);
    $this->assertArrayHasKey('authentication_token', $params);
  }

  /**
   * Tests ->delete() when a model doesn't have an ID
   * @return void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelDeleteException
   */
  public function testDeleteWithAnonymous ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['post-get-success.json'], $transactions);
    $model = new Model(null, false, $client);

    $model->delete($client);
    $this->assertCount(0, $transactions);
  }
}
