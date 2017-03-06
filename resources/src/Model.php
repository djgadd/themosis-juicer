<?php

namespace Com\KeltieCochrane\Juicer;

use Com\KeltieCochrane\Juicer\Client;

abstract class Model
{
  /**
   * The models ID
   * @var  int
   */
  protected $id;

  /**
   * Bleep bloop
   * @param  int|null  $id
   * @param  bool|null  $load
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  void
   */
  public function __construct (int $id = null, bool $load = null, Client $client = null)
  {
    $this->id = $id;

    if ($load) {
      $this->load($client);
    }
  }
}
