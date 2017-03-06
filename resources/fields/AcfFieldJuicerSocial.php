<?php

namespace Com\KeltieCochrane\Juicer\Fields;

use acf_field as AcfField; // Bcos ew.
use Themosis\Facades\View;
use Themosis\Facades\Config;
use Com\KeltieCochrane\Juicer\Factory as Juicer;

class AcfFieldJuicerSocial extends AcfField {

  /**
   * @var  string
   */
  public $name = 'juicer-social';

  /**
   * @var  string
   */
  public $label = 'Juicer Social';

  /**
   * @var  string
   */
  public $category = 'choice';

  /**
   * @var array
   */
  public $defaults = [
  ];

  /**
   * @var  array
   */
  public $l10n = [
    'error'	=> 'Error! Please enter a higher value',
  ];

  /**
   * @var  array
   */
  protected $settings;

  /**
   * Bleep bloop
   * @param  array  $settings
   * @return  void
   */
	public function __construct (array $settings)
  {
		$this->settings = $settings;
    $this->defaults['sources'] = [];

  	parent::__construct();
	}

  /**
   * Create settings for the field, these are visible when editing a field
   * @param  \  $field
   * @return  void
   */
  public function render_field_settings (array $field) {}

  /**
   * Render the field interface (displayed to the user)
   * @param  array  $field
   * @return  void
   */
	public function render_field (array $field)
  {
    echo View::make('com.keltiecochrane.juicer.fields.juicer-social')
      ->with([
        'field' => $field
      ])
      ->render();
	}

  /**
   * Load the value from the database
   * @param  mixed  $value
   * @param  int  $post_id
   * @param  array  $field
   */
	public function load_value ($value, $post_id, $field)
  {
    // Filter sources that might have been removed
    return array_intersect($value, array_keys($this->getSources()));
	}

  /**
   * Filter a value before it's saved to the DB
   * @param  mixed  $value
   * @param  int  $post_id
   * @param  array  $field
   */
	public function update_value ($value, $post_id, $field)
  {
		return $value;
	}

  /**
   * Validates the field
   * @param  bool  $valid
   * @param  mixed  $value
   * @param  array  $field
   * @param  string  $input
   * @return  mixed
   */
	public function validate_value ($valid, $value, $field, $input)
  {
    if (empty($value)) {
      $valid = 'You need to select a social network source.';
    }

		return $valid;
	}

  /**
   * Filters the field after it's been loaded from the DB
   * @param  array  $field
   * @return  array
   */
	public function load_field ($field)
  {
    $field['sources'] = $this->getSources();
		return $field;
	}

  /**
   * Returns an array of sources that can be used
   * @return  array
   */
  protected function getSources ()
  {
    return container('juicer')->feed(Config::get('juicer.slug'))->sources()->get()->mapWithKeys(function ($source) {
      return [$source->id => "{$source->generateAnchor()} ({$source->source})"];
    })->toArray();
  }
}
