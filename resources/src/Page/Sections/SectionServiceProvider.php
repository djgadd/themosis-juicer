<?php
namespace Com\KeltieCochrane\Juicer\Page\Sections;

use Themosis\Page\Sections\SectionData;
use Themosis\Foundation\ServiceProvider;

class SectionServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->bind('sections', function () {
      return new SectionBuilder(new SectionData());
    });
  }
}
