<?php

namespace Com\KeltieCochrane\Juicer\Source;

use Themosis\Facades\Config;
use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Source\Model;
use Com\KeltieCochrane\Juicer\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
  /**
   * Builds the query and returns a collection of post models
   * @param  bool  $missCache
   * @return \Illuminate\Support\Collection
   */
  public function get (bool $missCache = null) : Collection
  {
    $endpoint = Config::get('com_keltiecochrane_juicer_endpoints.feeds').'/'.$this->slug;
    $response = $this->client->request('GET', $endpoint, $this->query);
    $sources = new Collection(json_decode($response->getBody())->sources);

    return $sources->map(function ($post) {
      return Model::hydrate((array) $post);
    });
  }
}
