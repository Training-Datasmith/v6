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
use Elastic\Transport\Transport;
use Http\Promise\Promise;
use Psr\Http\Message\Request_Interface;
use Psr\Log\Logger_Interface;
interface Client_Interface
{
    /**
     * Get the Elastic\Transport\Transport
     */
    public function get_transport(): Transport;
    /**
     * Get the PSR-3 logger
     */
    public function get_logger(): Logger_Interface;
    /**
     * Set the asyncronous HTTP request
     */
    public function set_async(bool $async): self;
    /**
     * Get the asyncronous HTTP request setting
     */
    public function get_async(): bool;
    /**
     * Enable or disable the x-elastic-client-meta header
     */
    public function set_elastic_meta_header(bool $active): self;
    /**
     * Get the status of x-elastic-client-meta header
     */
    public function get_elastic_meta_header(): bool;
    /**
     * Enable or disable the response Exception
     */
    public function set_response_exception(bool $active): self;
    /**
     * Get the status of response Exception
     */
    public function get_response_exception(): bool;
    /**
     * Send the HTTP request using the Elastic Transport.
     * It manages syncronous and asyncronus requests using Client::getAsync()
     *
     * @return Elasticsearch|Promise
     */
    public function send_request(Request_Interface $request);
}