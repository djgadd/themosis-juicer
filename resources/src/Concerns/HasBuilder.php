<?php

namespace Com\KeltieCochrane\Juicer\Concerns;

use InvalidArgumentException;
use Com\KeltieCochrane\Juicer\Model;
use Com\KeltieCochrane\Juicer\Builder;

trait HasBuilder
{
  /**
   * @var \Com\KeltieCochrane\Juicer\Builder
  **/
  protected $builder;

  /**
   * Return the class path to the Model's Builder
   * @return  string
   */
  abstract public static function getBuilderClass () : string;

  /**
   * Create a new model with a builder
   * @param  \Com\KeltieCochrane\Juicer\Builder|string  $builder
   * @param  string|null  $slug
   * @return \Com\KeltieCochrane\Juicer\Model
   */
  public static function newWithBuilder ($builder, string $slug = null) : Model
  {
    if (!is_a($builder, Builder::class)) {
      if (!is_string($builder)) {
        throw new InvalidArgumentException('Argument 1 of newWithBuilder must be either a classname or a Builder');
      }

      if (is_null($slug)) {
        throw new InvalidArgumentException('Argument 2 of newWithBuilder must be a string');
      }

      $builder = new $builder($slug);
    }

    return (new static)->setBuilder($builder);
  }

  /**
   * Sets the builder for a model
   * @param  \Com\KeltieCochrane\Juicer\Builder  $builder
   * @return  \Com\KeltieCochrane\Juicer\Model
   */
  protected function setBuilder (Builder $builder) : Model
  {
    $this->builder = $builder;
    return $this;
  }

  /**
   * Magically maps static calls to dynamic calls by instantiating the Model.
   * @var  string  $method
   * @var  array  $vars
   * @return  mixed
  **/
  public static function __callStatic (string $method, array $vars)
  {
    return call_user_func_array([static::newWithBuilder(), $method], $vars);
  }

  /**
   * Magically calls methods on the Builder.
   * @var String $method
   * @var Array $vars
   * @return Mixed
  **/
  public function __call (string $method, array $vars)
  {
    return call_user_func_array([$this->builder, $method], $vars);
  }
}
