<?php

use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Source\Model;
use Com\KeltieCochrane\Juicer\Source\Builder;

class SourceBuilderTest extends PHPUnit_Framework_TestCase
{
  /**
   * Tests ->get()
   * @return void
   */
  public function testGet ()
  {
    // Mock Guzzle
    $client = create_mock_client(200, [], 'feed-get-success.json');
    $builder = new Builder('keltie-cochrane', $client);

    // Get our mocked sources
    $sources = $builder->get();

    // Validate that we got a Collection of Models back
    $this->assertInstanceOf(Collection::class, $sources);
    $this->assertInstanceOf(Model::class, $sources->first());

    // Validate that we can access properties
    $this->assertEquals(64592, $sources->first()->id);
  }
}
