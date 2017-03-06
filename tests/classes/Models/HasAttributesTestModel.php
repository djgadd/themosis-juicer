<?php

namespace Com\KeltieCochrane\Juicer\Tests\Models;

use Com\KeltieCochrane\Juicer\Client;
use Com\KeltieCochrane\Juicer\Model as BaseModel;
use Com\KeltieCochrane\Juicer\Concerns\HasAttributes;

class HasAttributesTestModel extends BaseModel
{
  use HasAttributes;

  /**
   * Load will change this to true so we can test it was lazy loaded
   * @var  bool
   */
  public $loaded = false;

  /**
   * Loads a models attributes from the API
   * @param  bool  $missCache
   * @param  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  \Com\KeltieCochrane\Juicer\Post\Model
   */
  protected function load (bool $missCache = null, Client $client = null) : BaseModel
  {
    $attributes = [
      'key' => 'lazy-loaded',
    ];

    $this->loaded = true;
    $this->setAttributes($attributes);
    return $this;
  }
}
