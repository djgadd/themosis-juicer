<?php

use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Com\KeltieCochrane\Juicer\Client;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Creates a mock Guzzle handler and returns a Client to use with builder
 * @param  int  $code
 * @param  array  $headers
 * @param  mixed  $files
 * @return  Client
 */
function create_mock_client (int $code, array $headers, $files, array &$container = [])
{
  if (!array_key_exists('content-type', $headers)) {
    $headers['content-type'] = 'appliation/json; charset=utf-8';
  }

  if (!is_array($files)) {
    $files = [$files];
  }

  $mock = new MockHandler(array_map(function ($file) use ($code, $headers) {
    $data = '';

    if (file_exists($file = __DIR__.'/files/data/'.$file)) {
      $data = file_get_contents($file);
    }

    return new Response($code, $headers, $data);
  }, $files));

  $handler = HandlerStack::create($mock);
  $history = Middleware::history($container);
  $handler->push($history);

  return new Client(new GuzzleClient(['handler' => $handler]));
}

/**
 * Reflects on a class and returns a protected/private method allowing it to be
 * invoked and tested
 * @var  string  $class
 * @var  string  $method
 * @return  \ReflectionMethod
 */
function get_class_method (string $class, string $method) : ReflectionMethod
{
  $class = new ReflectionClass($class);
  $method = $class->getMethod($method);
  $method->setAccessible(true);
  return $method;
}

/**
 * Reflects on a class and returns protected/private property allowing it to be
 * accessed and tested
 * @var  string  $class
 * @var  string  $method
 * @return  \ReflectionProperty
 */
function get_class_property(string $class, string $property) : ReflectionProperty
{
  $class = new ReflectionClass($class);
  $property = $class->getProperty($property);
  $property->setAccessible(true);
  return $property;
}

/**
 * Function used to test as a callback in ActionTest class.
 */
function actionHookCallback()
{
    // Run some code...
}

/**
 * Function used to test as a callback in FilterTest class.
 */
function callingForUncharted()
{
    // Run some code...
}

/**
 * Function used to test as a callback in AjaxTest class.
 */
function ajaxCallback()
{
    // Run some code...
}
