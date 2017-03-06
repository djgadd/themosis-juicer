<?php

namespace Com\KeltieCochrane\Juicer\Controllers;

use Themosis\Facades\Page;
use Themosis\Facades\View;
use Illuminate\Http\Request;
use Themosis\Route\BaseController;

class Admin extends BaseController
{
  /**
   * Called at after_setup_theme.
   * @return  void
   */
  public static function setup ()
  {
    $instance = new self;
  }

  /**
   * @var  \Themosis\Page\PageBuilder
   */
  protected $page;

  /**
   * Bleep bloop.
   * @return  void
   */
  public function __construct ()
  {
    // Page view
    $view = View::make('com.keltiecochrane.juicer.admin.layout');

    // Create the page
    $this->page = Page::make('themosis-juicer', 'Social Settings', 'options-general.php', $view)
      ->set([
        'capability' => 'manage_options',
        'icon' => 'dashicons-admin-site',
        'tabs' => true,
        'menu' => 'Social',
      ]);

    // Add the sections after we handle input
    $this->page->addSections($this->getSections());
  }

  /**
   * Builds the sections and handles any logic for them
   * @return  array
   */
  protected function getSections () : array
  {
    $request = Request::capture();

    return [
      Post::getSection($request),
      Source::getSection($request),
    ];
  }
}
