<?php

namespace Com\KeltieCochrane\Juicer\Feed;

use Themosis\Facades\Config;
use Com\KeltieCochrane\Juicer\Client;
use Com\KeltieCochrane\Juicer\Model as BaseModel;
use Com\KeltieCochrane\Juicer\Concerns\CanBeMutated;
use Com\KeltieCochrane\Juicer\Concerns\HasAttributes;
use Com\KeltieCochrane\Juicer\Builder as BaseBuilder;
use Com\KeltieCochrane\Juicer\Post\Model as PostModel;
use Com\KeltieCochrane\Juicer\Source\Model as SourceModel;
use Com\KeltieCochrane\Juicer\Exceptions\InvalidFrequencyException;

class Model extends BaseModel
{
  use HasAttributes, CanBeMutated;

  /**
   * Attributes that can be modified
   * @var  array
   */
  protected static $fillable = [
    'name',
    'update_frequency'
  ];

  /**
   * The frequencies that the feed can be updated to
   * @var  array
   */
  protected static $frequencies = [
    '24h' => 1440,
    '4h' => 240,
    '1h' => 60,
    '30m' => 30,
    '10m' => 10,
    '5m' => 5,
    '1m' => 1,
    'archive' => 0,
  ];

  /**
   * Override models constructor, our first param is a slug not an ID
   * @param  string  $slug
   * @param  bool|null  $load
   * @return void
   */
  public function __construct (string $slug, bool $load = null)
  {
    $this->id = $slug;

    if ($load) {
      $this->load();
    }
  }

  /**
   * Pushes the models state to Juicer.io
   * @param  array  $attributes
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  bool
   */
  protected function pushToJuicer (array $attributes, Client $client) : bool
  {
    // Validate the frequency if we have one
    if (array_key_exists('update_frequency', $attributes) && !in_array($attributes['update_frequency'], static::$frequencies, true)) {
      throw new InvalidFrequencyException;
    }

    // Add the id back in
    $attributes['id'] = $this->attributes['id'];

    // Create a client if we need it
    if (is_null($client)) {
      $client = new Client;
    }

    // Quezry das endpunt.
    $endpoint = Config::get('com_keltiecochrane_juicer_endpoints.feeds').'/'.$this->id;
    $client->request('PUT', $endpoint, $attributes, [], true);

    return true;
  }

  /**
   * Loads a models attributes from the API
   * @param  bool  $useCache
   * @param  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  \Com\KeltieCochrane\Juicer\Post\Model
   */
  protected function load (bool $missCache = null, Client $client = null) : Model
  {
    if (!$missCache && !empty($feed = container('juicer.cache')->tags('feeds')->get($this->id))) {
      $this->setAttributes($feed);
      return $this;
    }

    if (is_null($client)) {
      $client = new Client;
    }

    $endpoint = Config::get('com_keltiecochrane_juicer_endpoints.feeds').'/'.$this->id;
    $response = $client->request('GET', $endpoint, [
      'per' => 0, // We don't want any posts
    ]);

    // Get rid of posts to avoid any confusion
    $feed = json_decode($response->getBody(), true);
    unset($feed['posts']);
    unset($feed['sources']);

    // Cache
    container('juicer.cache')->tags('feeds')->put($this->id, $feed, $feed['update_frequency']);

    $this->setAttributes($feed);

    return $this;
  }

  /**
   * Returns a post Builder
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  \Com\KeltieCochrane\Juicer\Post}\Builder
   */
  public function posts (Client $client = null) : BaseBuilder
  {
    $builder = PostModel::getBuilderClass();
    return new $builder($this->id, $client);
  }

  /**
   * Shortcut to delete a post
   * @param  int  $id
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  bool
   */
  public function deletePost (int $id, Client $client = null) : bool
  {
    return (new PostModel($id, false))->delete($client);
  }

  /**
   * Returns a source Builder
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  \Com\KeltieCochrane\Juicer\Source\Builder
   */
  public function sources (Client $client = null) : BaseBuilder
  {
    $builder = SourceModel::getBuilderClass();
    return new $builder($this->id, $client);
  }

  /**
   * Shortcut to create a source
   * @var  string  $source
   * @var  string  $term
   * @var  string  $termType
   * @var  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  \Com\KeltieCochrane\Juicer\Source\Model
   */
  public function createSource (string $source, string $term, string $termType, Client $client = null) : SourceModel
  {
    // Load the feed if we don't have it
    if (empty($this->attributes)) {
      $this->load(true, $client);
    }

    return SourceModel::create($this->attributes['id'], $source, $term, $termType, $client);
  }

  /**
   * Shortcut to delete a source
   * @param  int  $id
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  bool
   */
  public function deleteSource (int $id, Client $client = null) : bool
  {
    $model = new SourceModel($this->id, $id, false);
    return $model->delete($client);
  }
}
