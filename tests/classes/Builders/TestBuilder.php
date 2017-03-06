<?php

namespace Com\KeltieCochrane\Juicer\Tests\Builders;

use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Builder as BaseBuilder;

class TestBuilder extends BaseBuilder
{
  public function get (bool $missCache = null) : Collection
  {
    return new Collection([]);
  }
}
