<?php

namespace Com\KeltieCochrane\Juicer;

use Carbon\Carbon;
use Themosis\Facades\Config;
use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Feed\Model;

abstract class Builder
{
  /**
   * The slug of the account the builder is querying
   * @var  string
   */
  protected $slug;

  /**
   * Holds the query parameters
   * @var array
   */
  protected $query = [];

  /**
   * Builds the query and returns a collection of post models
   * @param  bool  $missCache
   * @return \Illuminate\Support\Collection
   */
  abstract public function get (bool $missCache) : Collection;

  /**
   * Bleep bloop
   * @var  \GuzzleHttp\Client  $client
   * @var  string  $slug
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  void
   */
  public function __construct (string $slug, Client $client = null)
  {
    if (is_null($client)) {
      $client = new Client;
    }

    $this->slug = $slug;
    $this->client = $client;
  }

  /**
   * Returns the query
   * @return  array
   */
  public function getQuery () : array
  {
    return $this->query;
  }

  /**
   * Formats a Carbon date to a string format accepted by Juicer.io
   * @param  \Carbon\Carbon  $carbon
   * @return  string
   */
  protected function carbonToString (Carbon $carbon) : string
  {
    return $carbon->format('Y-m-d H:i');
  }

  /**
   * Creates a carbon date from a string forat used by Juicer.io
   * @param  string  $date
   * @return  \Carbon\Carbon
   */
  protected function stringToCarbon (string $date) : Carbon
  {
    return Carbon::createFromFormat('Y-m-d H:i', $date);
  }

  /**
   * Ensures there's a positive difference between the 2 dates
   * @param  \Carbon\Carbon  $from
   * @param  \Carbon\Carbon  $to
   * @return  bool
   */
  protected function isValidDateRange (Carbon $from = null, Carbon $to = null) : bool
  {
    if (is_null($from) || is_null($to)) {
      return true;
    }

    return $from < $to;
  }
}
