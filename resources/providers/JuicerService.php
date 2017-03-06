<?php

namespace Com\KeltieCochrane\Juicer\Services;

use Illuminate\Cache\CacheManager;
use Com\KeltieCochrane\Juicer\Factory;
use Themosis\Foundation\ServiceProvider;
use Com\KeltieCochrane\Cache\Drivers\WordPressStore;

class JuicerService extends ServiceProvider
{
  /**
   * Perform post-registration booting of services.
   * @return  void
  **/
  public function boot ()
  {
    container('juicer.cache')->extend('wordpress', function ($app) {
      global $wpdb;
      return container('juicer.cache')->repository(new WordPressStore($wpdb->prefix.'juicer_'));
    });
  }

  /**
   * Register plugin routes.
   * Define a custom namespace.
   * @return  void
   */
  public function register()
  {
    $this->app->singleton('juicer', function ($app) {
      return new Factory;
    });

    $this->app->singleton('juicer.cache', function ($app) {
      return new CacheManager($app);
    });
  }
}
