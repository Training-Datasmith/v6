<?php

/**
 * Elasticsearch PHP Client
 *
 * @link      https://github.com/elastic/elasticsearch-php
 * @copyright Copyright (c) Elasticsearch B.V (https://www.elastic.co)
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * Licensed to Elasticsearch B.V under one or more agreements.
 * Elasticsearch B.V licenses this file to you under the MIT License.
 * See the LICENSE file in the project root for more information.
 */
declare (strict_types=1);
namespace Elastic\Elasticsearch;

use Elastic\Elasticsearch\Exception\Authentication_Exception;
use Elastic\Elasticsearch\Exception\Config_Exception;
use Elastic\Elasticsearch\Exception\Http_Client_Exception;
use Elastic\Elasticsearch\Exception\InvalidArgumentException;
use Elastic\Elasticsearch\Transport\Adapter\Adapter_Interface;
use Elastic\Elasticsearch\Transport\Adapter\Adapter_Options;
use Elastic\Elasticsearch\Transport\Request_Options;
use Elastic\Transport\Node_Pool\Node_Pool_Interface;
use Elastic\Transport\Transport;
use Elastic\Transport\Transport_Builder;
use Http\Client\Http_Async_Client;
use Psr\Http\Client\Client_Interface;
use Psr\Log\Logger_Interface;
use ReflectionClass;
class Client_Builder
{
    public const DEFAULT_HOST = 'localhost:9200';
    /**
     * PSR-18 client
     */
    private Client_Interface $http_client;
    /**
     * The HTTP async client
     */
    private Http_Async_Client $async_http_client;
    /**
     * PSR-3 Logger
     */
    private Logger_Interface $logger;
    /**
     * The NodelPool
     */
    private Node_Pool_Interface $node_pool;
    /**
     * Hosts (elasticsearch nodes)
     */
    private array $hosts;
    /**
     * Elasticsearch API key
     */
    private string $api_key;
    /**
     * Basic authentication username
     */
    private string $username;
    /**
     * Basic authentication password
     */
    private string $password;
    /**
     * Elastic cloud Id
     */
    private string $cloud_id;
    /**
     * Retries
     *
     * The default value is calculated during the client build
     * and it is equal to the number of hosts
     */
    private int $retries;
    /**
     * SSL certificate
     * @var array [$cert, $password] $cert is the name of a file containing a PEM formatted certificate,
     *              $password if the certificate requires a password
     */
    private array $ssl_cert;
    /**
     * SSL key
     * @var array [$key, $password] $key is the name of a file containing a private SSL key,
     *              $password if the private key requires a password
     */
    private array $ssl_key;
    /**
     * SSL verification
     *
     * Enable or disable the SSL verfiication (default is true)
     */
    private bool $ssl_verification = true;
    /**
     * SSL CA bundle
     */
    private string $ssl_ca;
    /**
     * Elastic meta header
     *
     * Enable or disable the x-elastic-client-meta header (default is true)
     */
    private bool $elastic_meta_header = true;
    /**
     * HTTP client options
     */
    private array $http_client_options = [];
    /**
     * Make the constructor final so cannot be overwritten
     */
    final public function __construct()
    {
    }
    /**
     * Create an instance of ClientBuilder
     */
    public static function create(): Client_Builder
    {
        return new static();
    }
    /**
     * Build a new client from the provided config.  Hash keys
     * should correspond to the method name e.g. ['nodePool']
     * corresponds to setNodePool().
     *
     * Missing keys will use the default for that setting if applicable
     *
     * Unknown keys will throw an exception by default, but this can be silenced
     * by setting `quiet` to true
     *
     * @param  bool $quiet False if unknown settings throw exception, true to silently
     *                     ignore unknown settings
     * @throws ConfigException
     */
    public static function from_config(array $config, bool $quiet = false): Client
    {
        $builder = new static();
        foreach ($config as $key => $value) {
            $method = "set{$key}";
            $reflection = new ReflectionClass($builder);
            if ($reflection->has_method($method)) {
                $func = $reflection->get_method($method);
                if ($func->get_number_of_parameters() > 1) {
                    $builder->{$method}(...$value);
                } else {
                    $builder->{$method}($value);
                }
                unset($config[$key]);
            }
        }
        if ($quiet === false && count($config) > 0) {
            $unknown = implode('', array_keys($config));
            throw new Config_Exception("Unknown parameters provided: {$unknown}");
        }
        return $builder->build();
    }
    public function set_http_client(Client_Interface $http_client): Client_Builder
    {
        $this->http_client = $http_client;
        return $this;
    }
    public function set_async_http_client(Http_Async_Client $async_http_client): Client_Builder
    {
        $this->async_http_client = $async_http_client;
        return $this;
    }
    /**
     * Set the PSR-3 Logger
     */
    public function set_logger(Logger_Interface $logger): Client_Builder
    {
        $this->logger = $logger;
        return $this;
    }
    /**
     * Set the NodePool
     */
    public function set_node_pool(Node_Pool_Interface $node_pool): Client_Builder
    {
        $this->node_pool = $node_pool;
        return $this;
    }
    /**
     * Set the hosts (nodes)
     */
    public function set_hosts(array $hosts): Client_Builder
    {
        $this->hosts = $hosts;
        return $this;
    }
    /**
     * Set the ApiKey
     * If the id is not specified we store the ApiKey otherwise
     * we store as Base64(id:ApiKey)
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/security-api-create-api-key.html
     */
    public function set_api_key(string $api_key, string $id = null): Client_Builder
    {
        if (empty($id)) {
            $this->api_key = $api_key;
        } else {
            $this->api_key = base64_encode($id . ':' . $api_key);
        }
        return $this;
    }
    /**
     * Set the Basic Authentication
     */
    public function set_basic_authentication(string $username, string $password): Client_Builder
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }
    public function set_elastic_cloud_id(string $cloud_id): static
    {
        $this->cloud_id = $cloud_id;
        return $this;
    }
    /**
     * Set number or retries
     */
    public function set_retries(int $retries): Client_Builder
    {
        if ($retries < 0) {
            throw new InvalidArgumentException('The retries number must be >= 0');
        }
        $this->retries = $retries;
        return $this;
    }
    /**
     * Set SSL certificate
     *
     * @param string $cert The name of a file containing a PEM formatted certificate
     * @param string $password if the certificate requires a password
     */
    public function set_ssl_cert(string $cert, string $password = null): Client_Builder
    {
        $this->ssl_cert = [$cert, $password];
        return $this;
    }
    /**
     * Set the Certificate Authority (CA) bundle
     *
     * @param string $cert The name of a file containing a PEM formatted certificate
     */
    public function set_ca_bundle(string $cert): Client_Builder
    {
        $this->ssl_ca = $cert;
        return $this;
    }
    /**
     * Set SSL key
     *
     * @param string $key The name of a file containing a private SSL key
     * @param string $password if the private key requires a password
     */
    public function set_ssl_key(string $key, string $password = null): Client_Builder
    {
        $this->ssl_key = [$key, $password];
        return $this;
    }
    /**
     * Enable or disable the SSL verification
     */
    public function set_ssl_verification(bool $value = true): Client_Builder
    {
        $this->ssl_verification = $value;
        return $this;
    }
    /**
     * Enable or disable the x-elastic-client-meta header
     */
    public function set_elastic_meta_header(bool $value = true): Client_Builder
    {
        $this->elastic_meta_header = $value;
        return $this;
    }
    public function set_http_client_options(array $options): Client_Builder
    {
        $this->http_client_options = $options;
        return $this;
    }
    /**
     * Build and returns the Client object
     */
    public function build(): Client
    {
        // Transport builder
        $builder = Transport_Builder::create();
        // Set the default hosts if empty
        if (empty($this->hosts)) {
            $this->hosts = [self::DEFAULT_HOST];
        }
        $builder->set_hosts($this->hosts);
        // Logger
        if (!empty($this->logger)) {
            $builder->set_logger($this->logger);
        }
        // Http client
        if (!empty($this->http_client)) {
            $builder->set_client($this->http_client);
        }
        // Set HTTP client options
        $builder->set_client($this->set_options($builder->get_client(), $this->get_config(), $this->http_client_options));
        // Cloud id
        if (!empty($this->cloud_id)) {
            $builder->set_cloud_id($this->cloud_id);
        }
        // Node Pool
        if (!empty($this->node_pool)) {
            $builder->set_node_pool($this->node_pool);
        }
        $transport = $builder->build();
        // The default retries is equal to the number of hosts
        if (empty($this->retries)) {
            $this->retries = count($this->hosts);
        }
        $transport->set_retries($this->retries);
        // Async client
        if (!empty($this->async_http_client)) {
            $transport->set_async_client($this->async_http_client);
        }
        // Basic authentication
        if (!empty($this->username) && !empty($this->password)) {
            $transport->set_user_info($this->username, $this->password);
        }
        // API key
        if (!empty($this->api_key)) {
            if (!empty($this->username)) {
                throw new Authentication_Exception('You cannot use APIKey and Basic Authenication together');
            }
            $transport->set_header('Authorization', sprintf('ApiKey %s', $this->api_key));
        }
        /**
         * Elastic cloud optimized with gzip
         * @see https://github.com/elastic/elasticsearch-php/issues/1241 omit for Symfony HTTP Client
         */
        if (!empty($this->cloud_id) && !$this->is_symfony_http_client($transport)) {
            $transport->set_header('Accept-Encoding', 'gzip');
        }
        $client = new Client($transport, $transport->get_logger());
        // Enable or disable the x-elastic-client-meta header
        $client->set_elastic_meta_header($this->elastic_meta_header);
        return $client;
    }
    /**
     * Returns true if the transport HTTP client is Symfony
     */
    protected function is_symfony_http_client(Transport $transport): bool
    {
        if (str_contains($transport->get_client()::class, 'Symfony\Component\HttpClient')) {
            return true;
        }
        if (str_contains($transport->get_async_client()::class, 'Symfony\Component\HttpClient')) {
            return true;
        }
        return false;
    }
    /**
     * Returns the configuration to be used in the HTTP client
     */
    protected function get_config(): array
    {
        $config = [];
        if (!empty($this->ssl_cert)) {
            $config[Request_Options::SSL_CERT] = $this->ssl_cert;
        }
        if (!empty($this->ssl_key)) {
            $config[Request_Options::SSL_KEY] = $this->ssl_key;
        }
        if (!$this->ssl_verification) {
            $config[Request_Options::SSL_VERIFY] = false;
        }
        if (!empty($this->ssl_ca)) {
            $config[Request_Options::SSL_CA] = $this->ssl_ca;
        }
        return $config;
    }
    /**
     * Set the configuration for the specific HTTP client using an adapter
     */
    protected function set_options(Client_Interface $client, array $config, array $client_options = []): Client_Interface
    {
        if (empty($config) && empty($client_options)) {
            return $client;
        }
        $class = $client::class;
        if (!isset(Adapter_Options::HTTP_ADAPTERS[$class])) {
            throw new Http_Client_Exception(sprintf('The HTTP client %s is not supported for custom options', $class));
        }
        $adapter_class = Adapter_Options::HTTP_ADAPTERS[$class];
        if (!class_exists($adapter_class) || !in_array(Adapter_Interface::class, class_implements($adapter_class))) {
            throw new Http_Client_Exception(sprintf('The class %s does not exists or does not implement %s', $adapter_class, Adapter_Interface::class));
        }
        $adapter = new $adapter_class();
        return $adapter->set_config($client, $config, $client_options);
    }
}