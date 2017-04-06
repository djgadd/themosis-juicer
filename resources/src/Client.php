<?php

namespace Com\KeltieCochrane\Juicer;

use Themosis\Facades\Config;
use GuzzleHttp\Client as GuzzleClient;
use \Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\BadResponseException;
use Com\KeltieCochrane\Juicer\Exception\UnexpectedResponseException;

class Client
{
  /**
   * @var \GuzzleHttp\Client
   */
   protected $client;

  /**
   * Bleep bloop
   * @var  \GuzzleHttp\Client  $client
   * @return void
   */
  public function __construct (GuzzleClient $client = null)
  {
    if (is_null($client)) {
      $client = new GuzzleClient();
    }

    $this->client = $client;
  }

  /**
   * Builds the query and returns a response
   * @param  string  $method
   * @param  string  $endpoint
   * @param  array  $query
   * @param  array  $data
   * @param  bool  $auth
   * @return  \Psr\Http\Message\ResponseInterface
   * @throws  \Exception
   * @throws  \GuzzleHttp\Exception\BadResponseException
   * @throws  \Com\KeltieCochrane\Juicer\UnexpectedResponseException
   */
  public function request (string $method, string $endpoint, array $query = [], array $data = [], bool $auth = null) : ResponseInterface
  {
    $method = strtoupper($method);

    $options = [
      'headers' => [
        'User-Agent' => 'ThemosisJuicer/0.0.1',
      ],
      'query' => $query,
      'form_params' => $data,
      'timeout' => 1,
    ];

    // Set the auth token
    if ($auth === true) {
      $options['form_params']['authentication_token'] = Config::get('juicer.token');
    }

    $response =  $this->client->request($method, $endpoint, $options);

    // Check to see what response we got
    if ($response->getStatusCode() >= 300) {
      throw new UnexpectedResponseException($response->getStatusCode());
    }

    return $response;
  }
}
