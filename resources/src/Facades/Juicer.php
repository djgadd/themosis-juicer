<?php

namespace Com\KeltieCochrane\Juicer\Facades;

use Themosis\Facades\Facade;

class Juicer extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor ()
  {
    return 'juicer';
  }
}
