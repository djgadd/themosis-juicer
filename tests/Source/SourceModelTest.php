<?php

use Com\KeltieCochrane\Juicer\Source\Model;

class SourceModelTest extends PHPUnit_Framework_TestCase
{
  /**
   * Tests __construct without asking to load
   * @return  void
   */
  public function testNewWithoutLoad ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $model = new Model('keltie-cochrane', 64592, false, $client);

    // Ensure it didn't load anything
    $this->assertCount(0, $transactions);
  }

  /**
   * Tests __construct without asking to load
   * @return  void
   */
  public function testNewWithLoad ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $model = new Model('keltie-cochrane', 64592, true, $client);

    // Ensure we made a request
    $this->assertCount(1, $transactions);

    $transaction = $transactions[0];
    $this->assertEquals('GET', $transaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
    $this->assertEquals('/api/feeds/keltie-cochrane', $transaction['request']->getUri()->getPath());

    $this->assertEquals(64592, $model->id);
    $this->assertEquals('KELTIECOCHRANE', $model->term);
  }

  /**
   * Tests __construct without asking to load
   * @return void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelLoadException
   */
  public function testNewWithLoadAnonymous ()
  {
    $model = new Model('keltie-cochrane', null, true, null);
  }

  /**
   * Tests ::create
   * @return  void
   */
  public function testCreate ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $model = Model::create(21712, 'twitter', 'KELTIECOCHRANE', 'username', $client);

    // Ensure we made a request
    $this->assertCount(1, $transactions);

    $transaction = $transactions[0];
    $this->assertEquals('POST', $transaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
    $this->assertEquals('/api/sources', $transaction['request']->getUri()->getPath());

    $this->assertEquals(64592, $model->id);
    $this->assertEquals('KELTIECOCHRANE', $model->term);

    parse_str((string) $transaction['request']->getBody(), $params);
    $this->assertEquals(['feed_id', 'source', 'term', 'term_type', 'authentication_token'], array_keys($params));
  }

  /**
   * Tests ::create with an invalid source throws InvalidSourceException
   * @return  void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\InvalidSourceException
   */
  public function testCreateWithInvalidSourceThrowsInvalidSourceException ()
  {
    try {
      $transactions = [];
      $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
      $model = Model::create(21712, 'invalid', 'KELTIECOCHRANE', 'username', $client);
    }
    catch (\Com\KeltieCochrane\Juicer\Exceptions\InvalidSourceException $e) {
      $this->assertCount(0, $transactions);
      throw $e;
    }
  }

  /**
   * Tests ::create with an invalid source throws InvalidTermTypeException
   * @return  void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\InvalidTermTypeException
   */
  public function testCreateWithInvalidTermTypeThrowsInvalidTermTypeException ()
  {
    try {
      $transactions = [];
      $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
      $model = Model::create(21712, 'twitter', 'KELTIECOCHRANE', 'invalid', $client);
    }
    catch (\Com\KeltieCochrane\Juicer\Exceptions\InvalidTermTypeException $e) {
      $this->assertCount(0, $transactions);
      throw $e;
    }
  }

  /**
   * Tests ->delete
   * @return  void
   */
  public function testDelete ()
  {
    $transactions = [];
    $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
    $model = new Model('keltie-cochrane', 64592, false, $client);

    $this->assertTrue($model->delete($client));
    $this->assertCount(1, $transactions);

    $transaction = $transactions[0];
    $this->assertEquals('DELETE', $transaction['request']->getMethod());
    $this->assertEquals('www.juicer.io', $transaction['request']->getUri()->getHost());
    $this->assertEquals('/api/sources/64592', $transaction['request']->getUri()->getPath());

    parse_str((string) $transaction['request']->getBody(), $params);
    $this->assertArrayHasKey('authentication_token', $params);
  }

  /**
   * Tests ->delete anonymous model
   * @return  void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelDeleteException
   */
  public function testDeleteAnonymous ()
  {
    try {
      $transactions = [];
      $client = create_mock_client(200, [], ['feed-get-success.json'], $transactions);
      $model = new Model('keltie-cochrane', null, false, $client);
      $model->delete($client);
    }
    catch (Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelDeleteException $e) {
      $this->assertCount(0, $transactions);
      throw $e;
    }
  }
}
