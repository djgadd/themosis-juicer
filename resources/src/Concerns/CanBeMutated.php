<?php

namespace Com\KeltieCochrane\Juicer\Concerns;

use Com\KeltieCochrane\Juicer\Model;
use Com\KeltieCochrane\Juicer\Client;
use Com\KeltieCochrane\Juicer\Exceptions\NotFillableException;
use Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelSaveException;
use Com\KeltieCochrane\Juicer\Exceptions\NoFillablePropertiesException;
use Com\KeltieCochrane\Juicer\Exceptions\OriginalAttributesAlreadySetException;

trait CanBeMutated
{
  /**
   * Attributes that can be filled and sent to the server
   * @var  array
   */
  // protected static $fillable;

  /**
   * The Model's initial state
   * @var  array
   */
  protected $original;

  /**
   * Pushes the models state to Juicer.io
   * @param  array  $attributes
   * @param  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  bool
   */
  abstract protected function pushToJuicer (array $attributes, Client $client) : bool;

  /**
   * Saves a models state and calls the pushToJuicer method to ask the model to
   * perform the actual sync to the model
   * @param  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  bool
   * @throws  \Com\KeltieCochrane\Juicer\Exceptions\OriginalAttributesAlreadySetException
   */
  public function save (Client $client = null) : bool
  {
    if (is_null($this->id)) {
      throw new AnonymousModelSaveException;
    }

    // Get dirty attributes and filter so we only get the fillable ones
    $attributes = array_filter($this->getDirty($client), [$this, 'isFillable'], ARRAY_FILTER_USE_KEY);

    // We don't save if we're not dirty.
    if (!$this->isDirty($attributes)) {
      return false;
    }

    // Ask the model to perform the actual save
    if ($this->pushToJuicer($attributes, $client)) {
      // Update original
      $this->original = null;
      $this->setOriginalAttributes($this->getAttributes());

      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Syncs Model state with Juicer.io
   * @param  bool|null  $overrideDirty
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  \Com\KeltieCochrane\Juicer\Model
   */
  public function sync (bool $overrideDirty = null, Client $client = null) : Model
  {
    $this->original = null;

    if ($overrideDirty) {
      $this->attributes = [];
    }

    return $this->load(true, $client);
  }

  /**
   * Gets a Model's dirty attributes
   * @param  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  array
   */
  public function getDirty (Client $client = null) : array
  {
    $dirty = [];

    // If we haven't got an original to check on then load it!
    if (is_null($this->original)) {
      $this->load(true, $client);
    }

    foreach ($this->attributes as $key => $val) {
      if (!array_key_exists($key, $this->original)) {
        $dirty[$key] = $val;
      }
      elseif ($val !== $this->original[$key] && !$this->originalIsNumericallyEquivalent($key)) {
        $dirty[$key] = $val;
      }
    }

    return $dirty;
  }

  /**
   * Determines if a Model is dirty
   * @param  array $attributes
   * @return  bool
   */
  public function isDirty (array $attributes) : bool
  {
    $dirty = $this->getDirty();

    if (empty($attributes)) {
      return count($dirty) > 0;
    }

    // Check attributes to see if we have any dirty vals
    $keys = array_intersect_key($dirty, $attributes);

    return count($dirty) > 0;
  }

  /**
   * Returns an array of attributes that can be filled
   * @return  array
   */
  public function getFillable () : array
  {
    return static::$fillable ?: [];
  }

  /**
   * Determines if the given key is fillable or not
   * @param  string  $key
   * @return  bool
   */
  public function isFillable (string $key) : bool
  {
    return in_array($key, $this->getFillable());
  }

  /**
   * Sets the original attributes if they haven't already been set
   * @param  array  $attributes
   * @return  \Com\KeltieCochrane\Juicer\Model
   * @throws  \Com\KeltieCochrane\Juicer\Exceptions\OriginalAttributesAlreadySetException
   */
  protected function setOriginalAttributes (array $attributes) : Model
  {
    if (is_null($this->original)) {
      $this->original = $attributes;
      return $this;
    }

    // Really shouldn't be letting original be overriden so early
    throw new OriginalAttributesAlreadySetException();
  }

  /**
    * Determine if the new and old values for a given key are numerically equivalent.
    * @param  string  $key
    * @return bool
    */
   protected function originalIsNumericallyEquivalent ($key) : bool
   {
     $current = $this->attributes[$key];
     $original = $this->original[$key];
     return is_numeric($current) && is_numeric($original) && strcmp((string) $current, (string) $original) === 0;
   }

  /**
   * Magically sets attributes, lazy loads
   * @param  string  $key
   * @param  mixed  $val
   * @return  void
   */
  public function __set (string $key, $val)
  {
    if ($this->isFillable($key)) {
      $this->attributes[$key] = $val;
    }
    elseif (empty(static::$fillable)) {
      throw new NoFillablePropertiesException;
    }
    else {
      throw new NotFillableException;
    }
  }
}
