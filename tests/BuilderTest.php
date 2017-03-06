<?php

use Carbon\Carbon;
use Com\KeltieCochrane\Juicer\Client;
use Com\KeltieCochrane\Juicer\Tests\Builders\TestBuilder;

class BuilderTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var  \Com\KeltieCochrane\Juicer\Builder
   */
  protected $builder;

  /**
   * Sets up a builder for us to test
   * @return void
   */
  public function setUp ()
  {
    $this->builder = new TestBuilder('keltie-cochrane', new Client);
  }

  /**
   * Tests ->getQuery()
   * @return void
   */
  public function testGetQuery ()
  {
    $this->isType('array', $this->builder->getQuery());
    $this->assertCount(0, $this->builder->getQuery());
  }

  /**
   * Tests ->carbonToString() and ensures it's in a format we expect it to be in
   * @return  void
   */
  public function testCarbonToString ()
  {
    $carbonToString = get_class_method(TestBuilder::class, 'carbonToString');
    $string = $carbonToString->invokeArgs($this->builder, [
      Carbon::createFromTimestamp(0),
    ]);

    $this->assertEquals('1970-01-01 00:00', $string);
  }

  /**
   * Tests ->carbonToString() expects a Carbon object
   * @return  void
   * @expectedException  TypeError
   */
  public function testCarbonToStringExpectsCarbon ()
  {
    $carbonToString = get_class_method(TestBuilder::class, 'carbonToString');
    $string = $carbonToString->invokeArgs($this->builder, [
      new DateTime,
    ]);
  }

  /**
   * Tests ->stringToCarbon() converts a string into a carbon object
   * @return  void
   */
  public function testStringToCarbon ()
  {
    $test = Carbon::createFromTimestamp(0);
    $stringToCarbon = get_class_method(TestBuilder::class, 'stringToCarbon');
    $carbon = $stringToCarbon->invokeArgs($this->builder, [
      '1970-01-01 00:00',
    ]);

    $this->assertEquals($carbon, $test);
  }

  /**
   * Tests ->stringToCarbon() expects a string
   * @return  void
   * @expectedException  TypeError
   */
  public function testStringToCarbonExpectsString ()
  {
    $stringToCarbon = get_class_method(TestBuilder::class, 'stringToCarbon');
    $carbon = $stringToCarbon->invokeArgs($this->builder, [
      new DateTime,
    ]);
  }

  /**
   * Tests ->isValidDateRange
   * @return  void
   */
  public function testIsValidDateRange ()
  {
    $isValidDateRange = get_class_method(TestBuilder::class, 'isValidDateRange');
    $from = Carbon::createFromTimestamp(0);
    $to = carbon::createFromTimestamp(600);

    $this->assertTrue($isValidDateRange->invokeArgs($this->builder, [
      $from,
      $to,
    ]));

    $this->assertFalse($isValidDateRange->invokeArgs($this->builder, [
      $to,
      $from,
    ]));
  }

  /**
   * Tests ->isValidDateRange expects carbon
   * @return void
   * @expectedException  TypeError
   */
  public function testIsValidDateRangeExpectsCarbon ()
  {
    $isValidDateRange = get_class_method(TestBuilder::class, 'isValidDateRange');

    $isValidDateRange->invokeArgs($this->builder, [
      new DateTime,
      new DateTime,
    ]);
  }
}
