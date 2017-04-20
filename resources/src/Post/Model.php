<?php

namespace Com\KeltieCochrane\Juicer\Post;

use Com\KeltieCochrane\Juicer\Client;
use Com\KeltieCochrane\Juicer\Post\Builder;
use Com\KeltieCochrane\Juicer\Model as BaseModel;
use Com\KeltieCochrane\Juicer\Concerns\HasBuilder;
use Com\KeltieCochrane\Juicer\Concerns\HasAttributes;
use Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelLoadException;
use Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelDeleteException;

class Model extends BaseModel
{
  use HasBuilder, HasAttributes;

  /**
   * Return the class path to the Model's Builder
   * @return  string
   */
  static function getBuilderClass () : string
  {
    return Builder::class;
  }

  /**
   * Deletes a source by ID
   * @var  int  $id
   * @return  bool
   */
  public function delete (Client $client = null) : bool
  {
    if (is_null($this->id)) {
      throw new AnonymousModelDeleteException;
    }

    if (is_null($client)) {
      $client = new Client;
    }

    $endpoint = app('config')->get('com_keltiecochrane_juicer_endpoints.posts').'/'.$this->id;
    $response = $client->request('DELETE', $endpoint, [], [], true);
    return true;
  }

  /**
   * Loads a models attributes from the API
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return \Com\KeltieCochrane\Juicer\Model
   * @throws  \Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelLoadException
   */
  protected function load (Client $client = null) : Model
  {
    // Check we have an ID
    if (is_null($this->id)) {
      throw new AnonymousModelLoadException;
    }

    if (is_null($client)) {
      $client = new Client;
    }

    $endpoint = app('config')->get('com_keltiecochrane_juicer_endpoints.posts').'/'.$this->id;
    $response = $client->request('GET', $endpoint, [
      'per' => 0, // We don't want any posts
    ]);

    $this->setAttributes(json_decode($response->getBody(), true));

    return $this;
  }
}
