<?php

namespace Com\KeltieCochrane\Juicer\Tests\Models;

use Com\KeltieCochrane\Juicer\Client;
use Com\KeltieCochrane\Juicer\Model as BaseModel;
use Com\KeltieCochrane\Juicer\Concerns\HasAttributes;
use Com\KeltieCochrane\Juicer\Concerns\CanBeMutated;

class CanBeMutatedTestModel extends BaseModel
{
  use HasAttributes, CanBeMutated;

  /**
   * Load will change this to true so we can test it was lazy loaded
   * @var  bool
   */
  public $loaded = false;

  /**
   * Save will store it's stuff here
   * @var  array
   */
  public $saved;

  /**
   * Attributes that can be filled and sent to the server
   * @var  array
   */
  protected static $fillable = [
    'key',
  ];

  /**
   * Sends Model state to Juicer.io
   * @param  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  bool
   */
  protected function pushToJuicer (array $attributes, Client $client = null) : bool
  {
    if ($this->isDirty($this->attributes ?: [])) {
      return true;
    }
    else {
      return false;
    }
  }

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
