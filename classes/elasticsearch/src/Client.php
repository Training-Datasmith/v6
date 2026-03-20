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

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Elasticsearch\Traits\Client_Endpoints_Trait;
use Elastic\Elasticsearch\Traits\Endpoint_Trait;
use Elastic\Elasticsearch\Traits\Namespace_Trait;
use Elastic\Elasticsearch\Transport\Async_On_Success;
use Elastic\Elasticsearch\Transport\Async_On_Success_No_Exception;
use Elastic\Transport\Transport;
use Http\Promise\Promise;
use Psr\Http\Message\Request_Interface;
use Psr\Log\Logger_Interface;
final class Client implements Client_Interface
{
    use Client_Endpoints_Trait;
    use Endpoint_Trait;
    use Namespace_Trait;
    public const CLIENT_NAME = 'es';
    public const VERSION = '8.6.1';
    public const API_COMPATIBILITY_HEADER_7 = '%s/vnd.elasticsearch+%s; compatible-with=7';
    public const API_COMPATIBILITY_HEADER_8 = '%s/vnd.elasticsearch+%s; compatible-with=8';
    /**
     * Flag to indicate if the client is connected to Searchly
     */
    public static bool $is_searchly = false;
    /**
     * Specify is the request is asyncronous
     */
    protected bool $async = false;
    /**
     * Enable or disable the x-elastic-meta-header
     */
    protected bool $elastic_meta_header = true;
    /**
     * Enable or disable the response Exception
     */
    protected bool $response_exception = true;
    /**
     * The endpoint namespace storage
     */
    protected array $namespace;
    public function __construct(protected Transport $transport, protected Logger_Interface $logger)
    {
        $this->default_transport_settings($this->transport);
    }
    /**
     * @inheritdoc
     */
    public function get_transport(): Transport
    {
        return $this->transport;
    }
    /**
     * @inheritdoc
     */
    public function get_logger(): Logger_Interface
    {
        return $this->logger;
    }
    /**
     * Set the default settings for Elasticsearch
     */
    protected function default_transport_settings(Transport $transport): void
    {
        $transport->set_user_agent('elasticsearch-php', self::VERSION);
    }
    /**
     * @inheritdoc
     */
    public function set_async(bool $async): self
    {
        $this->async = $async;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function get_async(): bool
    {
        return $this->async;
    }
    /**
     * @inheritdoc
     */
    public function set_elastic_meta_header(bool $active): self
    {
        $this->elastic_meta_header = $active;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function get_elastic_meta_header(): bool
    {
        return $this->elastic_meta_header;
    }
    /**
     * @inheritdoc
     */
    public function set_response_exception(bool $active): self
    {
        $this->response_exception = $active;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function get_response_exception(): bool
    {
        return $this->response_exception;
    }
    /**
     * @inheritdoc
     */
    public function send_request(Request_Interface $request): \Http\Promise\Promise|\Elastic\Elasticsearch\Response\Elasticsearch
    {
        // If async returns a Promise
        if ($this->get_async()) {
            if ($this->get_elastic_meta_header()) {
                $this->transport->set_elastic_meta_header(Client::CLIENT_NAME, Client::VERSION, true);
            }
            $this->transport->set_async_on_success($request->get_method() === 'HEAD' ? new Async_On_Success_No_Exception() : ($this->get_response_exception() ? new Async_On_Success() : new Async_On_Success_No_Exception()));
            return $this->transport->send_async_request($request);
        }
        if ($this->get_elastic_meta_header()) {
            $this->transport->set_elastic_meta_header(Client::CLIENT_NAME, Client::VERSION, false);
        }
        $start = microtime(true);
        $response = $this->transport->send_request($request);
        $this->logger->info(sprintf('Response time in %.3f sec', microtime(true) - $start));
        $result = new Elasticsearch();
        $result->set_response($response, $request->get_method() === 'HEAD' ? false : $this->get_response_exception());
        return $result;
    }
}