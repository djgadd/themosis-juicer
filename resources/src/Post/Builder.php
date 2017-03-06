<?php

namespace Com\KeltieCochrane\Juicer\Post;

use stdClass;
use Exception;
use Carbon\Carbon;
use Themosis\Facades\Config;
use Illuminate\Support\Collection;
use Com\KeltieCochrane\Juicer\Post\Model;
use GuzzleHttp\Exception\BadResponseException;
use Com\KeltieCochrane\Juicer\Builder as BaseBuilder;
use Com\KeltieCochrane\Juicer\Exceptions\InvalidDateRangeException;
use Com\KeltieCochrane\Juicer\Exception\UnexpectedResponseException;

class Builder extends BaseBuilder
{
  /**
   * The additional filters that the post Builder can have
   * @var array
   */
  protected $filters = [];

  /**
   * Stores the from date
   * @var  \Carbon\Carbon
   */
  protected $from;

  /**
   * Stores the to date
   * @var  \Carbon\Carbon
   */
  protected $to;

  /**
   * @var  int
   */
  protected $page;

  /**
   * @var  int
   */
  protected $pageLength;

  /**
   * @var  \Illuminate\Support\Collection
   */
  protected $posts;

  /**
   * @var  \Carbon\Carbon
   */
  protected $expires;

  /**
   * Builds the query and returns a collection of post models
   * @param  bool $missCache
   * @return \Illuminate\Support\Collection
   */
  public function get (bool $missCache = null) : Collection
  {
    // If we can use the cache work out if we still need to query the API
    if (!$missCache) {
      $missCache = !$this->isCacheHit();

      // Have we got enough posts?
      if (!$missCache && $this->pageLength) {
        $missCache = $this->posts->count >= $this->pageLength;
      }
    }

    // We need to make a call to the API
    if ($missCache) {
      $this->query['filters'] = implode(',', $this->filters);
      $endpoint = Config::get('com_keltiecochrane_juicer_endpoints.feeds').'/'.$this->slug;

      // This should probably be asynchronous
      $body = json_decode($this->client->request('GET', $endpoint, $this->query)->getBody());

      // Collect and hydrate the posts
      $posts = collect($body->posts->items)->map(function ($post) {
        return Model::hydrate((array) $post);
      });

      // Persist to cache and store locally
      $this->setPosts($posts, Carbon::now()->addSeconds(60));
    }

    return $this->posts;
  }

  /**
   * Paginates the request and gets the request
   * @param  int  $page
   * @param  int  $pageLength
   * @param  bool $missCache
   * @return \Illuminate\Support\Collection
   */
  public function paginate (int $page, int $pageLength = 10, $missCache = null) : Collection
  {
    $this->invalidateBuilderCache();
    $this->page = $page;
    $this->pageLength = $pageLength;

    $this->query['page'] = $page;
    $this->query['per'] = $pageLength;

    return $this->get($missCache);
  }

  /**
   * Adds a filter to the query
   * @param  array|string  $filters
   * @return  \Com\KeltieCochrane\Juicer\Feed\Builder
   */
  public function filter ($filters) : Builder
  {
    $this->invalidateBuilderCache();

    if (is_array($filters)) {
      $filters = array_map(function ($filter) {
        return trim($filter);
      }, $filters);
    }
    else {
      $filters = [trim((string) $filters)];
    }

    $this->filters = array_unique(array_merge($this->filters, $filters), SORT_REGULAR);

    return $this;
  }

  /**
   * Get posts newer than this date
   * @param  \Carbon\Carbon  $from
   * @return  \Com\KeltieCochrane\Juicer\Feed\Builder
   */
  public function from (Carbon $from) : Builder
  {
    $this->invalidateBuilderCache();

    // Validate range
    if (!is_null($this->to) && !$this->isValidDateRange($from, $this->to)) {
      throw new InvalidDateRangeException;
    }

    $this->from = $from;
    $this->query['starting_at'] = $this->carbonToString($from);
    return $this;
  }

  /**
   * Get posts older than this date
   * @param  \Carbon\Carbon  $to
   * @return  \Com\KeltieCochrane\Juicer\Feed\Builder
   */
  public function to (Carbon $to) : Builder
  {
    $this->invalidateBuilderCache();

    // Validate range
    if (!is_null($this->from) && !$this->isValidDateRange($this->from, $to)) {
      throw new InvalidDateRangeException;
    }

    $this->to = $to;
    $this->query['ending_at'] = $this->carbonToString($to);
    return $this;
  }

  /**
   * Returns the array of filters
   * @return  array
   */
  public function getFilters () : array
  {
    return $this->filters;
  }

  /**
   * Filters a collection of posts by the filters in the builder, this is useful
   * when using cached posts
   * @param  \Illuminate\Support\Collection  $posts
   * @return  \Illuminate\Support\Collection
   */
  protected function filterPosts (Collection $posts) : Collection
  {
    $posts = $posts->filter(function ($post) {
      return $this->postMatchesQuery($post);
    });

    // Pageinate if needed
    if ($this->page && $this->pageLength) {
      return $posts->forPage($this->page, $this->pageLength);
    }

    return $posts;
  }

  /**
   * Determines whether a post matches the builders current query
   * @param  \Com\KeltieCochrane\Juicer\Post\Model $post
   * @return  bool
   */
  protected function postMatchesQuery (Model $post)
  {
    $createdAt = Carbon::parse($post->external_created_at);

    // Check if the source matches the filter
    $validSource = empty($this->filters) || // If we have no filters to apply
      in_array($post->source->id, $this->filters) || // Check for source ID
      in_array($post->source->term, $this->filters) ||  // Or check for source term
      in_array($post->source->source, $this->filters); // Or check for source

    // Is after from date if present
    $validFrom = is_null($this->from) || $createdAt->gt($this->from);

    // Is before to date if present
    $validTo = is_null($this->to) || $createdAt->lt($this->to);

    return $validSource && $validFrom && $validTo;
  }

  /**
   * Determines if the cache is valid still
   * @return  bool
   */
  protected function isCacheHit () : bool
  {
    // If our local copy hasn't got posts try pull them from the cache
    if (empty($this->posts)) {
      $this->posts = $this->filterPosts($this->getCachedPosts());
    }

    // If we don't know when the posts expire see if we can get it from the cache
    if (empty($this->expires)) {
      $this->expires = container('juicer.cache')
        ->tags(['posts', $this->slug])
        ->get('expires');
    }

    // If we have posts and the expirey is the in the future return true
    return !empty($this->posts) && !empty($this->expires) && $this->expires->isFuture();
  }

  /**
   * Gets posts filtered posts out of the cache
   * @return  \Illuminate\Support\Collection
   */
  protected function getCachedPosts () : Collection
  {
    $posts = container('juicer.cache')
      ->tags(['posts', $this->slug])
      ->get('posts');

    // Return an empty collection if we got nothing out of the cache
    if (empty($posts)) {
      return collect([]);
    }

    return $posts;
  }

  /**
   * Caches posts locally and appends them into our cache
   * @param  \Illuminate\Support\Collection  $posts
   * @param  \Carbon\Carbon  $expires
   * @return  void
   */
  protected function setPosts (Collection $posts, Carbon $expires)
  {
    // Store in our local builder cache
    $this->posts = $posts;
    $this->expires = $expires;

    // Persist the posts in the cache
    $this->cachePosts($posts);

    // Set the expire time on the posts
    container('juicer.cache')
      ->tags(['posts', $this->slug])
      ->forever('expires', $expires);
  }

  /**
   * Merges posts into the cache and makes sure the order is correct
   * @param  \Illuminate\Support\Collection  $posts
   * @return  void
   */
  protected function cachePosts (Collection $posts)
  {
    // Get all cached posts
    $cached = $this->getCachedPosts();

    if (!empty($cached)) {
      $posts = $posts->merge($cached)
        ->unique()
        ->sortByDesc(function ($post) {
          return Carbon::parse($post->external_created_at)->timestamp;
        });
    }

    // Flush the existing posts
    container('juicer.cache')
      ->tags(['posts', $this->slug])
      ->flush();

    // Cache 'em
    container('juicer.cache')
      ->tags(['posts', $this->slug])
      ->put('posts', $posts, Carbon::now()->diffInSeconds($this->expires));
  }

  /**
   * Invalidates the internal builder cache, used when changes to the query are made
   * @return void
   */
  protected function invalidateBuilderCache ()
  {
    $this->posts = null;
    $this->expires = null;
  }
}
