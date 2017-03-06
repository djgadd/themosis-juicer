<?php

namespace Com\KeltieCochrane\Juicer\Controllers;

use Exception;
use Themosis\Facades\View;
use Themosis\Facades\Config;
use Illuminate\Http\Request;
use Themosis\Facades\Section;
use Illuminate\Validation\Rule;
use Com\KeltieCochrane\Juicer\Factory as Juicer;
use Com\KeltieCochrane\Illuminate\Facades\Validator;
use Com\KeltieCochrane\Juicer\Page\Sections\SectionBuilder;

class Source extends Resource
{
  /**
   * The slug
   * @var  string
   */
  protected static $slug = 'com_keltiecochrane_juicer_sources';

  /**
   * The actions this controller can perform
   * @var  array
   */
  protected static $actions = [
    'create' => 'com_keltiecochrane_juicer_create_source',
    'delete' => 'com_keltiecochrane_juicer_delete_source',
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
    $view = View::make('com.keltiecochrane.juicer.admin.sources');

    return Section::make(static::$slug, 'Sources', [], $view)
      ->with([
        'networks' => Config::get('com_keltiecochrane_juicer_sources.networks'),
        'term_types' => Config::get('com_keltiecochrane_juicer_sources.term_types'),
        'sources' => container('juicer')->feed(Config::get('juicer.slug'))->sources()->get(),
        'errors' => $this->errors,
        'updates' => $this->updates,
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
      case static::$actions['create']:
        $this->create();
        break;

      case static::$actions['delete']:
        $this->delete();
        break;

      default:
        throw new Exception('unknown action');
    }
  }

  /**
   * Creates a new source
   * @return  bool
   */
  protected function create () : bool
  {
    $rules = [
      'network' => [
        'required',
        Rule::in(array_keys(Config::get('com_keltiecochrane_juicer_sources.networks'))),
      ],
      'term_type' => [
        'required',
        Rule::in(array_keys(Config::get('com_keltiecochrane_juicer_sources.term_types'))),
      ],
      'term' => [
        'required'
      ],
      '_wpnonce' => [
        'required',
        'verify_nonce:com_keltiecochrane_juicer_create_source',
      ],
    ];

    $validator = Validator::make($this->request->all(), $rules, [
      'network.required' => 'You must provide a social network for the new source.',
      'network.in' => 'You must provide a valid social network for the new source.',
      'term_type.required' => 'You must specify the term type for the new source.',
      'term_type.in' => 'You must provide a valid term type for the new source.',
      'term.required' => 'You must specify a term for the new source.',
      '_wpnonce.*' => 'The form was invalid, please refresh the page and try again.',
    ]);

    $this->errorsFromMessageBag($validator->errors());

    if ($validator->fails()) {
      return false;
    }

    try {
      $source = container('juicer')->feed(Config::get('juicer.slug'))->createSource($this->request->network, $this->request->term, $this->request->term_type);
      $this->updates['source_created'] = "Succesfully created {$source->generateUrl()}";
      return true;
    }
    catch (Exception $e) {
      $this->errors['generic'] = "Sorry, something went wrong: \"{$e->getMessage()}\"";
      return false;
    }
  }

  /**
   * Deletes an existing source
   * @return  bool
   */
  protected function delete ()
  {
    $rules = [
      'source_id' => 'required|integer',
      '_wpnonce' => [
        'required',
        'verify_nonce:com_keltiecochrane_juicer_delete_source',
      ],
    ];

    $validator = Validator::make($this->request->all(), $rules, [
      'source_id.*' => 'A valid source ID must be present',
      '_wpnonce.*' => 'The request was invalid, please refresh the page and try again.',
    ]);

    $this->errorsFromMessageBag($validator->errors());

    if ($validator->fails()) {
      return false;
    }

    try {
      $source = container('juicer')->feed(Config::get('juicer.slug'))->deleteSource($this->request->source_id);
      $this->updates['source_deleted'] = "Source deleted.";
      return true;
    }
    catch (Exception $e) {
      $this->errors['generic'] = "Sorry, something went wrong: \"{$e->getMessage()}\"";
      return false;
    }
  }
}
