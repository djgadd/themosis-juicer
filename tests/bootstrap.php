<?php
/**
 * PHPUnit bootstrap file.
 */
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir.'/includes/functions.php';

// Autoloader
require __DIR__.'/../vendor/autoload.php';

// Manually load in Themosis framework plugin
tests_add_filter('muplugins_loaded', function () {
  require dirname(dirname(__FILE__)).'/wp-content/plugins/framework/themosis.php';

  // Define the themosis_theme_assets function
  if (!function_exists('themosis_theme_assets')) {
      /**
       * Return the application theme public assets directory URL.
       * Public assets are stored into the `dist` directory.
       *
       * @return string
       */
      function themosis_theme_assets()
      {
          if (is_multisite() && SUBDOMAIN_INSTALL) {
              $segments = explode('themes', get_template_directory_uri());
              $theme = (strpos($segments[1], DS) !== false) ? substr($segments[1], 1) : $segments[1];

              return get_home_url().'/'.CONTENT_DIR.'/themes/'.$theme.'/dist';
          }

          return get_template_directory_uri().'/dist';
      }
  }
});

// Manually load our plugin
tests_add_filter('plugins_loaded', function () {
  require dirname(dirname(__FILE__)).'/themosis-juicer.php';
});

// Start up the WP testing environment.
require $_tests_dir.'/includes/bootstrap.php';

// Tell themosis to look for our test configs
container('config.finder')->addPaths([__DIR__.'/files/config/']);

// Define our providers
$providers = [
  KeltieCochrane\Illuminate\Config\ConfigServiceProvider::class,
  KeltieCochrane\Illuminate\Filesystem\FilesystemServiceProvider::class,
  KeltieCochrane\Illuminate\Translation\TranslationServiceProvider::class,
  KeltieCochrane\Illuminate\Validation\ValidationServiceProvider::class,
  KeltieCochrane\Logger\LogServiceProvider::class,
  KeltieCochrane\Cache\CacheServiceProvider::class,
];

// Register our providers
foreach ($providers as $provider) {
  container()->register($provider);
}

// Simple little autoloader for our classes
spl_autoload_register(function ($classname) {
  $dirs = array_diff_key(explode('\\', $classname), [0, 1, 2, 3]);
  $file = __DIR__.DS.'classes'.DS.implode(DS, $dirs).'.php';

  if (file_exists($file)) {
    include_once $file;
  }
});

// Add test_helpers.php file.
include 'test_helpers.php';
