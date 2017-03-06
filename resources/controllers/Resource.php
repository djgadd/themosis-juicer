<?php

namespace Com\KeltieCochrane\Juicer\Controllers;

use Themosis\Facades\Action;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Themosis\Route\BaseController;
use Com\KeltieCochrane\Juicer\Page\Sections\SectionBuilder;

abstract class Resource extends BaseController
{
  /**
   * Gets the source section
   * @param  \Illuminate\Http\Request  $request
   * @return  \Com\KeltieCochrane\Juicer\Page\Sections\SectionBuilder
   */
  public static function getSection (Request $request) : SectionBuilder
  {
    $instance = new static($request);
    return $instance->buildSection();
  }

  /**
   * @var  \Illuminate\Http\Request
   */
  protected $request;

  /**
   * Builds the section and returns it
   * @return  \Com\KeltieCochrane\Juicer\Page\Sections\SectionBuilder
   */
  abstract protected function buildSection () : SectionBuilder;

  /**
   * Handles the controller actions
   * @return  void
   * @throws
   */
  abstract protected function handleAction ();

  /**
   * Bleep bloop
   * @param  \Illuminate\Http\Request  $request
   * @return  void
   */
  protected function __construct (Request $request)
  {
    $this->request = $request;

    if ($request->has('action') && $request->has('option_page') && $request->option_page === static::$slug) {
      $this->handleAction();
    }
  }

  /**
   * Parses a message bag and adds them to errors
   * @param  \Illuminate\Support\MessageBag  $messageBag
   * @return  void
   */
  protected function errorsFromMessageBag (MessageBag $messageBag)
  {
    $fields = $messageBag->getMessages();

    foreach ($fields as $field => $messages) {
      foreach ($messages as $key => $message) {
        $this->errors["{$field}_{$key}"] = $message;
      }
    }
  }
}
