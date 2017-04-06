<?php

namespace Com\KeltieCochrane\Juicer\Controllers;

use Exception;
use Themosis\Facades\View;
use Themosis\Facades\Config;
use Illuminate\Http\Request;
use Themosis\Facades\Section;
use Illuminate\Validation\Rule;
use Com\KeltieCochrane\Logger\Facades\Log;
use Com\KeltieCochrane\Juicer\Factory as Juicer;
use Com\KeltieCochrane\Illuminate\Facades\Validator;
use Com\KeltieCochrane\Juicer\Page\Sections\SectionBuilder;

class Post extends Resource
{
  /**
   * The slug
   * @var  string
   */
  protected static $slug = 'com_keltiecochrane_juicer_posts';

  /**
   * The actions this controller can perform
   * @var  array
   */
  protected static $actions = [
    'delete' => 'com_keltiecochrane_juicer_delete_post',
  ];

  /**
   * An array of errors associated with the request
   * @var  array
   */
  protected $errors = [];

  /**
   * An array of updates associated with the request
   * @var  array
   */
  protected $updates = [];

  /**
   * Builds the section and returns it
   * @return  \Com\KeltieCochrane\Juicer\Page\Sections\SectionBuilder
   */
  protected function buildSection () : SectionBuilder
  {
    $view = View::make('com.keltiecochrane.juicer.admin.posts');
    $posts = [];

    try {
      Log::debug('Com\KeltieCochrane\Juicer\Controllers\Post@buildSection: getting posts');
      $posts = container('juicer')->feed(Config::get('juicer.slug'))->posts()->get();
    }
    catch (\Exception $e) {
      Log::error($e->getMessage(), [
        'exception' => $e
      ]);
      $this->errors['generic'] = "Sorry, something went wrong: \"{$e->getMessage()}\"";
    }

    return Section::make(static::$slug, 'Posts', [], $view)
      ->with([
        'errors' => $this->errors,
        'updates' => $this->updates,
        'posts' => $posts,
      ]);
  }

  /**
   * Handles the controller actions
   * @return  void
   * @throws
   */
  protected function handleAction ()
  {
    switch ($this->request->action) {
      case static::$actions['delete']:
        $this->delete();
        break;

      default:
        throw new Exception('unknown action');
    }
  }

  /**
   * Deletes an existing post
   * @return  bool
   */
  protected function delete ()
  {
    $rules = [
      'post_id' => 'required|integer',
      '_wpnonce' => [
        'required',
        'verify_nonce:com_keltiecochrane_juicer_delete_post',
      ],
    ];

    $validator = Validator::make($this->request->all(), $rules, [
      'post_id.*' => 'A valid source ID must be present',
      '_wpnonce.*' => 'The request was invalid, please refresh the page and try again.',
    ]);

    $this->errorsFromMessageBag($validator->errors());

    if ($validator->fails()) {
      return false;
    }

    try {
      $source = container('juicer')->feed(Config::get('juicer.slug'))->deletePost($this->request->post_id);
      $this->updates['post_deleted'] = "Post deleted.";
      return true;
    }
    catch (Exception $e) {
      $this->errors['generic'] = "Sorry, something went wrong: \"{$e->getMessage()}\"";
      return false;
    }
  }
}
