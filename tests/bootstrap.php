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

/**
 * Manually load the plugin being tested.
 */
tests_add_filter('muplugins_loaded', function () {
  require dirname(dirname(__FILE__)).'/plugins/framework/themosis.php';
});

/**
 * Load the other plugins and our plugin
 */
tests_add_filter('plugins_loaded', function () {
  require dirname(dirname(__FILE__)).'/plugins/themosis-cache/themosis-cache.php';
  require dirname(dirname(__FILE__)).'/themosis-juicer.php';
});

// Start up the WP testing environment.
require $_tests_dir.'/includes/bootstrap.php';

// Tell themosis to look for our test configs
container('config.finder')->addPaths([__DIR__.'/files/config/']);

// Register the config alias
class_alias(Themosis\Facades\Config::class, 'Config');

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
