<?php

namespace Lagdo\PolrAdmin;

use Lagdo\PolrAdmin\Helpers\Validator;

use Jaxon\Utils\Config\Config;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use Exception;

class Client
{
    /**
     * The configuration options of the package
     *
     * @var Config
     */
    protected $config;

    /**
     * The validation helper
     *
     * @var Validator
     */
    protected $validator;

    /**
     * The HTTP client
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * The constructor.
     *
     * @param Config    $config     The package options
     * @param Validator $validator  The validation helper
     */
    public function __construct(Config $config, Validator $validator)
    {
        $this->config = $config;
        $this->validator = $validator;
        $this->httpClient = new HttpClient();
    }

    protected function getRemoteAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    protected function request($server, $method, $path, array $params = [])
    {
        $params['key'] = $this->config->getOption("servers.$server.key");
        $uri = rtrim($this->config->getOption("servers.$server.url"), '/') . '/' .
            trim($this->config->getOption("servers.$server.api"), '/') . '/' . ltrim($path, '/');
        return $this->httpClient->$method($uri, ['query' => $params]);
    }

    public function getServer($server)
    {
        return (object)$this->config->getOption("servers.$server");
    }

    public function createShortUrl($server, array $values)
    {
        // Validate URL form data
        if(!$this->validator->validateLinkUrl($values, false))
        {
            throw new Exception('Invalid URL or custom ending.');
        }

        // API request parameters
        $parameters = [
            'url' => $values['url'],
            'secret' => ($values['options'] == "s" ? 'true' : 'false'),
            'ip' => $this->getRemoteAddress(),
        ];
        if($values['ending'] != '')
        {
            $parameters['ending'] = $values['ending'];
        }

        // Update the link in the Polr instance
        $apiResponse = $this->request($server, 'post', 'links', $parameters);
        $jsonResponse = json_decode($apiResponse->getBody()->getContents());
        return $jsonResponse->result;
    }

    public function checkAvailability($server, $ending)
    {
        $ending = trim($ending);
        // Validate the input
        if(!$this->validator->validateLinkEnding($ending))
        {
            throw new Exception('Invalid Custom URL Ending.');
        }

        // Fetch the link from the Polr instance
        try
        {
            $this->request($server, 'get', 'links/' . $ending);
            // if ending already exists
            return false;
        }
        catch(RequestException $e)
        {
            jaxon()->logger()->warning("Error calling Polr server $server: " . $e->getMessage());
            throw new Exception('Unable to check link availability.');
        }
        catch(ClientException $e)
        {
            return true;
        }
    }

    public function deleteShortUrl($server, $ending)
    {
        $ending = trim($ending);
        // Validate the input
        if(!$this->validator->validateLinkEnding($ending))
        {
            throw new Exception('Ending not valid.');
        }

        try
        {
            // Delete the link in the Polr instance
            $this->request($server, 'delete', 'links/' . $ending);
        }
        catch(Exception $e)
        {
            jaxon()->logger()->warning("Error calling Polr server $server: " . $e->getMessage());
            throw new Exception('Unable to delete the link.');
        }
    }

    protected function datatableParameters($parameters)
    {
        // The boolean parameters sent by Guzzle in a HTTP request are not recognized
        // by Datatables. So we need to convert them to strings "true" or "false".
        foreach($parameters['columns'] as &$column)
        {
            $column['searchable'] = ($column['searchable']) ? 'true' : 'false';
            $column['orderable'] = ($column['orderable']) ? 'true' : 'false';
            $column['search']['regex'] = ($column['search']['regex']) ? 'true' : 'false';
        }
        // Set the "key" parameter
        return $parameters;
    }

    public function getAllShortUrls($server, $parameters)
    {
        try
        {
            // Fetch the links from the Polr instance
            $apiResponse = $this->request($server, 'get', 'links', $this->datatableParameters($parameters));
            return json_decode($apiResponse->getBody()->getContents());
        }
        catch(Exception $e)
        {
            jaxon()->logger()->warning("Error calling Polr server $server: " . $e->getMessage());
            throw new Exception('Unable to fetch links.');
        }
    }

    public function getShortUrls($server, $parameters)
    {
        try
        {
            // Fetch the links from the Polr instance
            $apiResponse = $this->request($server, 'get', 'users/me/links', $this->datatableParameters($parameters));
            return json_decode($apiResponse->getBody()->getContents());
        }
        catch(Exception $e)
        {
            jaxon()->logger()->warning("Error calling Polr server $server: " . $e->getMessage());
            throw new Exception('Unable to fetch links.');
        }
    }

    public function getShortUrl($server, $ending)
    {
        $ending = trim($ending);
        // Validate the input
        if(!$this->validator->validateLinkEnding($ending))
        {
            throw new Exception('Ending not valid.');
        }

        try
        {
            // Fetch the link from the Polr instance
            $apiResponse = $this->request($server, 'get', 'links/' . $ending);
            $jsonResponse = json_decode($apiResponse->getBody()->getContents());
            return $jsonResponse->result;
        }
        catch(Exception $e)
        {
            jaxon()->logger()->warning("Error calling Polr server $server: " . $e->getMessage());
            throw new Exception('Unable to get link.');
        }
    }

    public function saveShortUrl($server, $ending, array $values)
    {
        $ending = trim($ending);
        // Validate the new URL
        if(!$this->validator->validateLinkEnding($ending))
        {
            throw new Exception('Ending not valid.');
        }
        if(!$this->validator->validateLinkUrl($values, false))
        {
            throw new Exception('Link not valid.');
        }

        try
        {
            // Update the link in the Polr instance
            $this->request($server, 'put', 'links/' . $ending, $values);
        }
        catch(Exception $e)
        {
            jaxon()->logger()->warning("Error calling Polr server $server: " . $e->getMessage());
            throw new Exception('Unable to save link.');
        }
    }

    public function getStats($server, $ending, $leftBound, $rightBound)
    {
        $ending = trim($ending);
        try
        {
            $stats = [];
            $path = ($ending === '' ? 'stats' : 'links/' . $ending . '/stats');
            $parameters = [
                'left_bound' => (string)$leftBound,
                'right_bound' => (string)$rightBound,
            ];

            // Fetch the stats from the Polr instance
            $parameters['type'] = 'day';
            $apiResponse = $this->request($server, 'get', $path, $parameters);
            $jsonResponse = json_decode($apiResponse->getBody()->getContents());
            $stats['day'] = $jsonResponse->result;

            // Fetch the stats from the Polr instance
            $parameters['type'] = 'country';
            $apiResponse = $this->request($server, 'get', $path, $parameters);
            $jsonResponse = json_decode($apiResponse->getBody()->getContents());
            $stats['country'] = $jsonResponse->result;

            // Fetch the stats from the Polr instance
            $parameters['type'] = 'referer';
            $apiResponse = $this->request($server, 'get', $path, $parameters);
            $jsonResponse = json_decode($apiResponse->getBody()->getContents());
            $stats['referer'] = $jsonResponse->result;

            return $stats;
        }
        catch(Exception $e)
        {
            jaxon()->logger()->warning("Error calling Polr server $server: " . $e->getMessage());
            throw new Exception('Unable to get stats.');
        }
    }
}
