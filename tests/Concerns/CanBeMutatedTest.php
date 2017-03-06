<?php

use Com\KeltieCochrane\Juicer\Tests\Models\CanBeMutatedTestModel;

/**
 * @group  Concerns
 */
class CanBeMutatedTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var CanBeMutatedTestModel
   */
  protected $model;

  /**
   * Setup a model for us to use
   * @return void
   */
  public function setUp ()
  {
    $this->model = new CanBeMutatedTestModel(1);
  }

  /**
   * Tests ->__set()
   * @return void
   */
  public function testSetters ()
  {
    $this->assertEquals('lazy-loaded', $this->model->key);
    $this->model->key = 'changed';
    $this->assertEquals('changed', $this->model->key);
  }

  /**
   * Tests that non fillable properties can't be changed
   * @return void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\NotFillableException
   */
  public function testFillableSetters ()
  {
    $this->model->not_fillable = 'change';
  }

  /**
   * Tests that the protected method ->setAttributes() merges and keeps our data
   * @return void
   */
  public function testSetAttributesMerges ()
  {
    $this->model->key = 'my value';
    $this->assertEquals('my value', $this->model->key);
    $this->assertFalse($this->model->loaded);

    // Make sure we get my value back
    $attributes = $this->model->getAttributes();
    $this->isType('array', $attributes);
    $this->assertEquals('my value', $attributes['key']);
    $this->assertFalse($this->model->loaded);

    // Now load the values and ensure we keep my value
    $attributes = $this->model->getAttributes(true);
    $this->isType('array', $attributes);
    $this->assertEquals('my value', $attributes['key']);
    $this->assertEquals('my value', $this->model->key);
    $this->assertTrue($this->model->loaded);
  }

  /**
   * Tests that ->setAttributes() calls setOriginalAttributes() and sets the
   * original attributes
   * @return void
   */
  public function testSetAttributesSetsOriginal ()
  {
    $attributes = $this->model->getAttributes(true);
    $original = get_class_property(CanBeMutatedTestModel::class, 'original');
    $this->assertEquals($attributes, $original->getValue($this->model));
  }

  /**
   * Tests ->getDirty()
   * @return void
   */
  public function testGetDirty ()
  {
    // Ensure we haven't loaded anything
    $this->assertFalse($this->model->loaded);

    // Test we get nothing on a clean model
    $dirty = $this->model->getDirty();
    $this->isType('array', $dirty);
    $this->assertCount(0, $dirty);

    // Ensure we have loaded the original in
    $this->assertTrue($this->model->loaded);

    // Change a value and check we get it back
    $this->model->key = 'changed';
    $dirty = $this->model->getDirty();
    $this->isType('array', $dirty);
    $this->assertCount(1, $dirty);
    $this->assertEquals('changed', $dirty['key']);
  }

  /**
   * Test ->isDirty()
   * @return void
   */
  public function testIsDirty ()
  {
    // We haven't made any changes so the model shouldn't be dirty
    $this->assertFalse($this->model->isDirty($this->model->getAttributes()));

    // Change a value and check the model is dirty
    $this->model->key = 'changed';
    $this->assertTrue($this->model->isDirty($this->model->getAttributes()));
  }

  /**
   * Test ->getFillable()
   * @return void
   */
  public function testGetFillable ()
  {
    $fillable = $this->model->getFillable();
    $this->isType('array', $fillable);
    $this->assertContains('key', $fillable);
  }

  /**
   * Test ->isFillable()
   * @return void
   */
  public function testIsFillable()
  {
    $this->assertTrue($this->model->isFillable('key'));
    $this->assertFalse($this->model->isFillable('not_fillable'));
  }

  /**
   * Test ->setOriginalAttributes()
   * @return void
   */
  public function testSetOriginalAttributes ()
  {
    $attributes = ['key' => 'value'];
    $setOriginalAttributes = get_class_method(CanBeMutatedTestModel::class, 'setOriginalAttributes');
    $original = get_class_property(CanBeMutatedTestModel::class, 'original');
    $setOriginalAttributes->invokeArgs($this->model, [
      $attributes,
    ]);

    $this->assertEquals($attributes, $original->getValue($this->model));
  }

  /**
   * Test ->setOriginalAttributes() can only be called once
   * @return void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\OriginalAttributesAlreadySetException
   */
  public function testSetOriginalAttributesMoreThanOnceFails ()
  {
    $attributes = ['key' => 'value'];
    $setOriginalAttributes = get_class_method(CanBeMutatedTestModel::class, 'setOriginalAttributes');
    $setOriginalAttributes->invokeArgs($this->model, [
      $attributes,
    ]);

    // This should fail
    $setOriginalAttributes->invokeArgs($this->model, [
      $attributes,
    ]);
  }

  /**
   * Tests ->save()
   * @return  void
   */
  public function testSave ()
  {
    // Nothing changed so it shouldn't need to save
    $this->assertFalse($this->model->save());

    // Set a value so it can save
    $this->model->key = 'my value';
    $this->assertTrue($this->model->save());
  }

  /**
   * Tests -> save on an anonymous model
   * @return  void
   * @expectedException  \Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelSaveException
   */
  public function testSaveAnonymous ()
  {
    try {
      $transactions = [];
      $client = create_mock_client(200, [], [], $transactions);
      $this->model = new CanBeMutatedTestModel;
      $this->model->save();
    }
    catch (Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelSaveException $e) {
      $this->assertCount(0, $transactions);
      throw $e;
    }
  }

  /**
   * Tests ->sync()
   * @return  void
   */
  public function testSync ()
  {
    $attributes = $this->model->getAttributes(true);
    $original = get_class_property(CanBeMutatedTestModel::class, 'original');

    // Make sure we loaded
    $this->assertEquals(['key' => 'lazy-loaded'], $attributes);
    $this->assertEquals(['key' => 'lazy-loaded'], $original->getValue($this->model));

    // Override key and check we got it
    $this->model->key = 'my value';
    $attributes = $this->model->getAttributes();
    $this->assertEquals(['key' => 'my value'], $attributes);

    // Sync the model and check we overrided the dirty attributes
    $model = $this->model->sync(true);
    $attributes = $this->model->getAttributes();
    $this->assertInstanceOf(CanBeMutatedTestModel::class, $model);
    $this->assertEquals(['key' => 'lazy-loaded'], $original->getValue($this->model));
    $this->assertEquals(['key' => 'lazy-loaded'], $attributes);
  }
}
