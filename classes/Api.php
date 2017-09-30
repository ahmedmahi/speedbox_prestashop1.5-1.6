<?php

/**
 * @author      Speedbox ( http://www.speedbox.ma)
 * @copyright   2017 Speedbox  ( http://www.speedbox.ma)
 * @developer   Ahmed MAHI <1hmedmahi@gmail.com> (http://ahmedmahi.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$dir = dirname(__FILE__);

// plumbing
require_once $dir . '/Api/Authentication.php';
require_once $dir . '/Api/Request.php';

// exceptions
require_once $dir . '/Api/Exceptions.php';

// resources
require_once $dir . '/Api/Resources/Abstract.php';
require_once $dir . '/Api/Resources/Colis.php';
require_once $dir . '/Api/Resources/Pointsrelais.php';
require_once $dir . '/Api/Resources/Villes.php';
class S3ibusiness_Speedbox_Model_Api
{

    const VERSION = '1.0.0';

    public $store_url;

    public $consumer_key;

    public $consumer_secret;

    public $api_url;

    public $validate_url = false;

    public $timeout = 30;

    public $ssl_verify = true;

    /** Resources */

    public $villes;

    public $colis;

    public $points_relais;

    public function __construct($arguments = array())
    {

        // required functions
        if (!extension_loaded('curl')) {
            throw new Exception('Speedbox REST API  requires the cURL PHP extension.');
        }

        if (!extension_loaded('json')) {
            throw new Exception('Speedbox REST API  needs the JSON extension.');
        }

        // set required info
        $this->store_url       = $arguments['store_url'];
        $this->consumer_key    = $arguments['consumer_key'];
        $this->consumer_secret = $arguments['consumer_secret'];

        // load each API resource
        $this->init_resources();

        // build API url from store URL
        $this->build_api_url();

        // set options
        $this->parse_options($arguments['options']);

        if ($this->validate_url) {
            $this->validate_api_url();
        }
    }

    public function init_resources()
    {

        $resources = array(
            'S3ibusiness_Speedbox_Model_Api_Resources_Villes'       => 'villes',
            'S3ibusiness_Speedbox_Model_Api_Resources_Colis'        => 'colis',
            'S3ibusiness_Speedbox_Model_Api_Resources_Pointsrelais' => 'points_relais',
        );

        foreach ($resources as $resource_class => $resource_method) {
            $this->$resource_method = new $resource_class($this);
        }
    }

    public function build_api_url()
    {

        $url = parse_url($this->store_url);

        // default to http if not provided
        $scheme = isset($url['scheme']) ? $url['scheme'] : 'http';

        // set host
        $host = $url['host'];

        // add port to host if provided
        $host .= isset($url['port']) ? ':' . $url['port'] : '';

        // set path and strip any trailing slashes
        $path = isset($url['path']) ? rtrim($url['path'], '/') : '';

        // add api path
        $path .= '/api/';

        // build URL
        $this->api_url = "{$scheme}://{$host}{$path}";
    }

    public function parse_options($options)
    {

        $valid_options = array(
            'validate_url',
            'timeout',
            'ssl_verify',
        );

        foreach ((array) $options as $opt_key => $opt_value) {

            if (!in_array($opt_key, $valid_options)) {
                continue;
            }

            $this->$opt_key = $opt_value;
        }
    }

    public function validate_api_url()
    {

        $index = @file_get_contents($this->api_url);

        if (false === $index) {
            throw new S3ibusiness_Speedbox_Model_Api_Exceptions(sprintf('Invalid URL, no SPEEDBOX API found at %s -- ensure your store URL is correct and pretty permalinks are enabled.', $this->api_url), 404);
        }

        if ('1' === $index) {
            throw new S3ibusiness_Speedbox_Model_Api_Exceptions(sprintf('Please upgrade the Magento version on %s to 1.4 or greater.', $this->api_url));
        }

        $json_start = strpos($index, '{');
        $json_end   = strrpos($index, '}') + 1;

        $index = json_decode(substr($index, $json_start, ($json_end - $json_start)));

        if (null === $index) {
            throw new S3ibusiness_Speedbox_Model_Api_Exceptions(sprintf('SPEEDBOX API found, but JSON is corrupt -- ensure the index at %s is valid JSON.', $this->api_url));
        }

        if ('https' === parse_url($index->store->URL, PHP_URL_SCHEME) && !$index->store->meta->ssl_enabled) {

            $this->api_url = str_replace('http://', 'https://', $this->api_url);
        }
    }

    public function make_api_call($method, $path, $request_data, $is_auth = 0)
    {

        $args = array(
            'is_auth'         => $is_auth,
            'method'          => $method,
            'url'             => $this->api_url . $path,
            'base_url'        => $this->api_url,
            'data'            => $request_data,
            'consumer_key'    => $this->consumer_key,
            'consumer_secret' => $this->consumer_secret,
            'options'         => array(
                'timeout'    => $this->timeout,
                'ssl_verify' => $this->ssl_verify,
            ),
        );
        $request = new S3ibusiness_Speedbox_Model_Api_Request($args);

        return $request->dispatch();
    }

}
