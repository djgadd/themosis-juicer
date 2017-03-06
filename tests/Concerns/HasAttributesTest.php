<?php

use Com\KeltieCochrane\Juicer\Tests\Models\HasAttributesTestModel;

/**
 * @group  Concerns
 */
class HasAttributesTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var  HasAttributesTestModel
   */
  protected $model;

  /**
   * Sets up a model for us to test
   * @return void
   */
  public function setUp ()
  {
    $this->model = new HasAttributesTestModel;
  }

  /**
   * Tests ::hydrate()
   * @return void
   */
  public function testHydrate ()
  {
    $model = HasAttributesTestModel::hydrate([
      'id' => 1,
      'key' => 'hydrated',
    ]);

    $this->assertInstanceOf(HasAttributesTestModel::class, $model);
    $this->assertTrue(isset($model->key));
    $this->assertEquals('hydrated', $model->key);
  }

  /**
   * Tests that the model lazy loaded properly
   * @return void
   */
  public function testLazyLoad ()
  {
    $this->assertFalse($this->model->loaded);
    $this->assertTrue(isset($this->model->key));
  }

  /**
   * Tests ->__get()
   * @return void
   */
  public function testGet ()
  {
    $this->assertEquals('lazy-loaded', $this->model->key);
  }

  /**
   * Tests ->getAttributes()
   * @return void
   */
  public function testGetAttributes ()
  {
    // Make sure we haven't loaded anything
    $this->assertFalse($this->model->loaded);

    // Get the attributes
    $this->isType('array', $this->model->getAttributes());
    $this->assertFalse($this->model->loaded); // We didn't ask it to load anything

    // Now explicitly tell it to load
    $attributes = $this->model->getAttributes(true);
    $this->assertTrue($this->model->loaded);
    $this->isType('array', $attributes);
    $this->assertEquals('lazy-loaded', $this->model->key);
  }
}
