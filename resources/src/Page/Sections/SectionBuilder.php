<?php

namespace Com\KeltieCochrane\Juicer\Page\Sections;

use Themosis\Page\Sections\SectionBuilder as ThemosisSectionBuilder;

class SectionBuilder extends ThemosisSectionBuilder
{
  /**
   * Render the sections view
   * @return void
   */
  public function render ()
  {
    if (!empty($this->shared['errors'])) {
      foreach ($this->shared['errors'] as $field => $message) {
        add_settings_error($field, $field, $message, 'error');
      }
    }

    if (!empty($this->shared['updates'])) {
      foreach ($this->shared['updates'] as $field => $message) {
        add_settings_error($field, $field, $message, 'updated');
      }
    }

    settings_errors();
    $this->shared['__section'] = $this;
    echo $this->view->with($this->shared)->render();
  }

  /**
   * Get properties from $data
   * @param  string  $key
   * @return  mixed
   */
  public function __get (string $key)
  {
    return $this->data[$key];
  }
}
