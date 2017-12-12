<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\TransferException;
use Monolog\Logger;

class AddressFinderService
{
    private static $api_url = 'https://oauth.nzpost.co.nz/as/token.oauth2';

    private static $auth_url = 'https://oauth.nzpost.co.nz/';

    private $client;

    private $enable_auth = false;

    private $enable_logging = false;

    public function __construct()
    {
        $this->client = $this->createClient();
    }

    public function createClient()
    {
        $stack = new GuzzleHttp\HandlerStack();
        $stack->setHandler(new GuzzleHttp\Handler\CurlHandler());

        if ($this->enable_logging) {
            $stack->push($this->addLoggingMiddleware('{method} {uri} HTTP/{version} {code} Request: {req_headers} {req_body} - Response: {res_headers} {res_body}'));
        }

        if ($this->enable_auth) {
            if (!$this->isAuthorised()) {
                $this->authorise();
                $stack->push($this->addAuthorizationHeader());
            }
        }

        $client = new Client([
            'base_uri' => Config::inst()->get('NZPOSTAPI', 'base_url'),
            'handler' => $stack,
            'headers' => ['Accept' => 'application/json'],
            'timeout' => 5,
            'http_errors' => true
        ]);

        return $client;
    }

    public function addLoggingMiddleware($messageFormat)
    {
        $log = new Logger('NZPostLogger');
        $log->pushHandler(new Monolog\Handler\StreamHandler(__DIR__.'/nzpost_debug.log', Logger::DEBUG));
        return \GuzzleHttp\Middleware::log(
            $log,
            new \GuzzleHttp\MessageFormatter($messageFormat)
        );
    }

    public function isAuthorised()
    {
        return ($this->getSession()->getToken()) ? true : false;
    }

    public function authorise()
    {
        if ($this->isAuthorised()) {
            return true;
        }

        $stack = new GuzzleHttp\HandlerStack();
        $stack->setHandler(new GuzzleHttp\Handler\CurlHandler());

        $client = new Client([
            'base_uri' => Config::inst()->get('NZPOSTAPI', 'auth_url'),
            'handler' => $stack,
            'connect_timeout' => 5,
            'timeout' => 3
        ]);

        $json = array(
            'grant_type' => 'client_credentials',
            'client_id' => Config::inst()->get('NZPOSTAPI', 'client_id'),
            'client_secret' => Config::inst()->get('NZPOSTAPI', 'client_secret')
        );

        try {
            $response = $client->post('as/token.oauth2', array('query' => $json));
            $responseData = $this->decodeResponse($response);

            $this->getSession()->setToken($responseData->access_token, $responseData->expires_in);
        } catch (Exception $e) {
            $this->handleError($e);
        } finally {
            return $responseData;
        }
    }

    private function addAuthorizationHeader()
    {
        $self = $this;
        return function (callable $handler) use ($self) {
            return function (Psr\Http\Message\RequestInterface $request, array $options) use ($self, $handler) {
                if ($token = $self->getSession()->getToken()) {
                    $request = $request->withHeader('Authorization', 'Bearer ' . $token);
                }

                return $handler($request, $options);
            };
        };
    }

    public function getSession()
    {
        return new NZPostApiSession;
    }

    /**
     * Decode the response
     * @param  [type] $response [description]
     * @return [type]           [description]
     */
    public function decodeResponse($response)
    {
        $body = $response->getBody()->getContents();

        if (empty($body)) {
            return null;
        }

        $data = json_decode($body);

        return $data;
    }

    /**
     * Log our errors
     * @param Exception $error
     */
    public function handleError($error)
    {
    }

    public function getSuggestions($query)
    {
        $json = [
            'q' => $query,
            'client_id' => Config::inst()->get('NZPOSTAPI', 'client_id'),
            'client_secret' => Config::inst()->get('NZPOSTAPI', 'client_secret')
        ];

        try {
            $response = $this->client->get('suggest', ['query' => $json]);
            $responseData = $this->decodeResponse($response);
            //
            // var_dump($responseData);
            // die();
        } catch (RequestException $e) {
            SS_Log::log($e->getMessage(), SS_Log::DEBUG);
            throw new Exception($e->getMessage(), 1);
        }
        return $responseData;
    }

    public function getDetails($dpid) {
        $json = [
            'dpid' => $dpid,
            'client_id' => Config::inst()->get('NZPOSTAPI', 'client_id'),
            'client_secret' => Config::inst()->get('NZPOSTAPI', 'client_secret')
        ];

        try {
            $response = $this->client->get('details', ['query' => $json]);
            $responseData = $this->decodeResponse($response);

            // var_dump($responseData);
        } catch (RequestException $e) {
            SS_Log::log($e->getMessage(), SS_Log::DEBUG);
            throw new Exception($e->getMessage(), 1);
        }
        return $responseData;
    }
}
