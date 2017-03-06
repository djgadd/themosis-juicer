<?php

use Com\KeltieCochrane\Juicer\Tests\Builders\TestBuilder;
use Com\KeltieCochrane\Juicer\Tests\Models\HasBuilderTestModel;

use Com\KeltieCochrane\Juicer\Client;

/**
 * @group  Concerns
 */
class HasBuilderTest extends PHPUnit_Framework_TestCase
{
  /**
   * Tests ::newWithBuilder()
   * @return void
   */
  public function testNewWithBuilder ()
  {
    $model = HasBuilderTestModel::newWithBuilder(new TestBuilder('keltie-cochrane', new Client));
    $this->assertInstanceOf(HasBuilderTestModel::class, $model);
  }

  /**
   * Tests ::newWithBuilder() will only accept a Builder
   * @return void
   * @expectedException  \InvalidArgumentException
   */
  public function testNewWithBuilderOnlyAcceptsBuilder ()
  {
    $model = HasBuilderTestModel::newWithBuilder(new stdClass);
  }
}
