<?php

namespace Com\KeltieCochrane\Juicer\Source;

use Themosis\Facades\Config;
use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Client;
use Com\KeltieCochrane\Juicer\Model as BaseModel;
use Com\KeltieCochrane\Juicer\Concerns\HasBuilder;
use Com\KeltieCochrane\Juicer\Concerns\CanBeMutated;
use Com\KeltieCochrane\Juicer\Concerns\HasAttributes;
use Com\KeltieCochrane\Juicer\Exceptions\InvalidSourceException;
use Com\KeltieCochrane\Juicer\Exceptions\InvalidTermTypeException;
use Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelLoadException;
use Com\KeltieCochrane\Juicer\Exceptions\AnonymousModelDeleteException;

class Model extends BaseModel
{
  use HasAttributes, HasBuilder;

  /**
   * The valid sources
   * @var  array
   */
  protected static $sources = [
    'flickr' => 'Flickr',
    'tumblr' => 'Tumblr',
    'youtube' => 'YouTube',
    'blog' => 'Blog',
    'googleplus' => 'GooglePlus',
    'deviantart' => 'DeviantArt',
    'instagram' => 'Instagram',
    'linkedin' => 'LinkedIn',
    'yelp' => 'Yelp',
    'vine' => 'Vine',
    'twitter' => 'Twitter',
    'soundcloud' => 'Soundcloud',
    'pinterest' => 'Pinterest',
    'facebook' => 'Facebook',
    'spotify' => 'Spotify',
    'slack' => 'Slack',
    'vimeo' => 'Vimeo',
  ];

  /**
   * The valid term types
   * @var  array
   */
  protected static $termTypes = ['username', 'hashtag'];

  /**
   * The feed slug
   * @var  string
   */
  protected $slug;

  /**
   * Return the class path to the Model's Builder
   * @return  string
   */
  public static function getBuilderClass () : string
  {
    return Builder::class;
  }

  /**
   * Bleep bloop
   * @param  string  $slug
   * @param  int|null  $id
   * @param  bool|null  $load
   * @param  \Com\KeltieCochrane\Juicer\Client|null  $client
   * @return  void
   */
  public function __construct (string $slug, int $id = null, bool $load = null, Client $client = null)
  {
    $this->slug = $slug;
    $this->id = $id;

    if ($load) {
      $this->load($client);
    }
  }

  /**
   * Generates a URL linking to the original source
   * @return  string
   */
  public function generateAnchor () : string
  {
    $sources = array_flip(Config::get('com_keltiecochrane_juicer_sources.networks'));
    $source = $sources[$this->source];

    switch ($source) {
      case 'twitter':
        return $this->generateTwitterAnchor();

      case 'tumblr':
        return $this->generateTumblrAnchor();

      case 'youtube':
        return $this->generateYouTubeAnchor();

      case 'blog':
        return $this->generateBlogAnchor();

      case 'googleplus':
        return $this->generateGooglePlusAnchor();

      case 'deviantart':
        return $this->generateDeviantArtAnchor();

      case 'instagram':
        return $this->generateInstagramAnchor();

      case 'linkedin':
        return $this->generateLinkedInAnchor();

      case 'yelp':
        return $this->generateYelpAnchor();

      case 'vine':
        return $this->generateVineAnchor();

      case 'soundcloud':
        return $this->generateSoundCloudAnchor();

      case 'pinterest':
        return $this->generatePinterestAnchor();

      case 'facebook':
        return $this->generateFacebookAnchor();

      case 'spotify':
        return $this->generateSpotifyAnchor();

      case 'slack':
        return $this->generateSlackAnchor();

      case 'vimeo':
        return $this->generateVimeoAnchor();

      default:
        return "{$this->term_type} - {$this->term}";
    }
  }

  /**
   * Generates a URL linking to the original Twitter source
   * @return  string
   */
  protected function generateTwitterAnchor () : string
  {
    $url = 'https://twitter.com';

    switch ($this->term_type) {
      case 'username':
        $url = "{$url}/{$this->term}";
        return "<a href=\"{$url}\" target=\"_blank\">@{$this->term}</a>";

      case 'hashtag':
        $url = "{$url}/hashtag/{$this->term}";
        return "<a href=\"{$url}\" target=\"_blank\">#{$this->term}</a>";

      default:
        return $this->term;
    }
  }

  /**
   * Generates a URL linking to the original Instagram source
   * @return  string
   */
  protected function generateInstagramAnchor () : string
  {
    $url = 'https://instagram.com';

    switch ($this->term_type) {
      case 'username':
        $url = "{$url}/{$this->term}";
        return "<a href=\"{$url}\" target=\"_blank\">@{$this->term}</a>";

      case 'hashtag':
        $url = "{$url}/explore/tags/{$this->term}";
        return "<a href=\"{$url}\" target=\"_blank\">#{$this->term}</a>";

      default:
        return $this->term;
    }
  }

  /**
   * Generates a URL linking to the original Facebook source
   * @return  string
   */
  protected function generateFacebookAnchor () : string
  {
    $url = 'https://facebook.com';

    switch ($this->term_type) {
      case 'username':
        $url = "{$url}/{$this->term}";
        return "<a href=\"{$url}\" target=\"_blank\">@{$this->term}</a>";

      case 'hashtag':
        $url = "{$url}/search/top/?q=%23{$this->term}";
        return "<a href=\"{$url}\" target=\"_blank\">#{$this->term}</a>";

      default:
        return $this->term;
    }
  }

  /**
   * Creates a new source with Juicer.io and returns the source Model
   * @var  int  $feedId
   * @var  string  $source
   * @var  string  $term
   * @var  string  $termType
   * @var  \Com\KeltieCochrane\Juicer\Client  $client
   * @return  \Com\KeltieCochrane\Juicer\Source\Model
   */
  public static function create (int $feedId, string $source, string $term, string $termType, Client $client = null) : Model
  {
    // Ensure we have a valid source
    $source = static::validateSource($source);

    // Ensure we have a valid termType
    $termType = static::validateTermType($termType);

    // I can't think of a better way to inject a client for testing than this...
    if (is_null($client)) {
      $client = new Client;
    }

    // Make the request
    $endpoint = Config::get('com_keltiecochrane_juicer_endpoints.sources');
    $response = $client->request('POST', $endpoint, [], [
      'feed_id' => $feedId,
      'source' => $source,
      'term' => $term,
      'term_type' => $termType,
    ], true);

    $source = (new Collection(json_decode($response->getBody())->sources))
      ->first(function ($source) use ($term, $termType) {
        return $source->term == $term && strtolower($source->term_type) == $termType;
      });

    return static::hydrate((array) $source);
  }

  /**
   * Deletes a source by ID
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

    $endpoint = Config::get('com_keltiecochrane_juicer_endpoints.sources').'/'.$this->id;
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

    // We have to get the feed to load in the required source, which is bleak
    $id = $this->id;
    $endpoint = Config::get('com_keltiecochrane_juicer_endpoints.feeds').'/'.$this->slug;
    $response = $client->request('GET', $endpoint, ['per' => 0]);
    $source = (new Collection(json_decode($response->getBody())->sources))
      ->first(function ($source) use ($id) {
        return $source->id === $id;
      });

    $this->setAttributes((array) $source);

    return $this;
  }

  /**
   * Returns a validated source string
   * @param  string  $source
   * @return  string
   * @throws  \Com\KeltieCochrane\Juicer\Exceptions\InvalidSourceException
   */
  protected static function validateSource (string $source) : string
  {
    $source = strtolower($source);

    if (array_key_exists($source, static::$sources)) {
      return static::$sources[$source];
    }
    else {
      throw new InvalidSourceException("'{$source}' isn't a valid Juicer.io source.");
    }
  }

  /**
   * Returns a validated source string
   * @param  string  $termType
   * @return  string
   * @throws  \Com\KeltieCochrane\Juicer\Exceptions\InvalidTermTypeException
   */
  protected static function validateTermType (string $termType) : string
  {
    $termType = strtolower($termType);

    if (in_array($termType, static::$termTypes)) {
      return $termType;
    }
    else {
      throw new InvalidTermTypeException("'{$termType}' isn't a valud Juicer.io term type.");
    }
  }
}
