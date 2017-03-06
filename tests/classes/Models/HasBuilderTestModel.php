<?php

namespace Com\KeltieCochrane\Juicer\Tests\Models;

use Com\KeltieCochrane\Juicer\Model as BaseModel;
use Com\KeltieCochrane\Juicer\Concerns\HasBuilder;
use Com\KeltieCochrane\Juicer\Tests\Builders\TestBuilder;

class HasBuilderTestModel extends BaseModel
{
  use HasBuilder;

  /**
   * Return the class path to the Model's Builder
   * @return  string
   */
  static function getBuilderClass () : string
  {
    return TestBuilder::class;
  }
}
