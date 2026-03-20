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
namespace Elastic\Elasticsearch\Traits;

use Psr\Http\Message\Stream_Interface;
/**
 * Proxy class for Psr\Http\Message\ResponseInterface using
 * $this->response as source object
 */
trait Message_Response_Trait
{
    public function get_protocol_version()
    {
        return $this->response->get_protocol_version();
    }
    public function with_protocol_version($version)
    {
        return $this->response->with_protocol_version($version);
    }
    public function get_headers()
    {
        return $this->response->get_headers();
    }
    public function has_header($name)
    {
        return $this->response->has_header($name);
    }
    public function get_header($name)
    {
        return $this->response->get_header($name);
    }
    public function get_header_line($name)
    {
        return $this->response->get_header_line($name);
    }
    public function with_header($name, $value)
    {
        return $this->response->with_header($name, $value);
    }
    public function with_added_header($name, $value)
    {
        return $this->response->with_added_header($name, $value);
    }
    public function without_header($name)
    {
        return $this->response->without_header($name);
    }
    public function get_body()
    {
        return $this->response->get_body();
    }
    public function with_body(Stream_Interface $body)
    {
        return $this->response->with_body($body);
    }
    public function get_status_code()
    {
        return $this->response->get_status_code();
    }
    public function with_status($code, $reason_phrase = '')
    {
        return $this->response->with_status($code, $reason_phrase);
    }
    public function get_reason_phrase()
    {
        return $this->response->get_reason_phrase();
    }
}