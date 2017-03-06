<?php

namespace Com\KeltieCochrane\Juicer\Concerns;

use Com\KeltieCochrane\Juicer\Model;
use Com\KeltieCochrane\Juicer\Client;

trait HasAttributes
{
  /**
   * An array of of the Model's attributes
   * @var  array
   */
  protected $attributes;

  /**
   * Loads a models attributes from the API
   * @param  bool  $useCache
   * @param  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  \Com\KeltieCochrane\Juicer\Post\Model
   */
  abstract protected function load (bool $missCache, Client $client) : Model;

  /**
   * Creates a model, sets it's attributes and returns is
   * @param  array  $attributes
   * @return  \Com\KeltieCochrane\Juicer\Post\Model
   */
  public static function hydrate (array $attributes) : Model
  {
    return (new static($attributes['id'], false))->setAttributes($attributes, true);
  }

  /**
   * Returns the attributes
   * @param  bool|null  $load
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  array
   */
  public function getAttributes (bool $load = null, Client $client = null) : array
  {
    if ($load === true) {
      $this->load(false, $client);
    }

    return $this->attributes ?: [];
  }

  /**
   * Sets the attributes on a Model, checks to see if we need to set a copy of
   * the original attributes and merges the array keeping our existing changes if
   * any.
   * @param  array  $attributes
   * @return  \Com\KeltieCochrane\Juicer\Post\Model
   */
  protected function setAttributes (array $attributes) : Model
  {
    // Check to see if we need to set original
    if (method_exists($this, 'setOriginalAttributes') && is_null($this->original)) {
      $this->setOriginalAttributes($attributes);
    }

    // Set to an empty array so we can have a clean one liner
    if (is_null($this->attributes)) {
      $this->attributes = [];
    }

    // Merge existing attributes into the incoming attributes to ensure we preserve changes
    $this->attributes = array_merge($attributes, $this->attributes);

    return $this;
  }

   /**
    * Magically maps to attributes
    * @param  string $key
    * @return  bool
    */
   public function __isset (string $key) : bool
   {
     // Lazy load
     if (empty($this->attributes)) {
       $this->load();
     }

     return isset($this->attributes[$key]);
   }

   /**
    * Magically maps to attributes
    * @param  string $key
    * @return  mixed
    */
   public function __get (string $key)
   {
     if (empty($this->attributes)) {
       $this->load();
     }

     return $this->attributes[$key];
   }
}
