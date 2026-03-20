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

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\Content_Type_Exception;
use Elastic\Elasticsearch\Exception\Missing_Parameter_Exception;
use Elastic\Transport\Serializer\Json_Serializer;
use Elastic\Transport\Serializer\Nd_Json_Serializer;
use Http\Discovery\Psr17factory_Discovery;
use function http_build_query;
use Psr\Http\Message\Request_Interface;
use function sprintf;
trait Endpoint_Trait
{
    /**
     * Check if an array containts nested array
     */
    private function is_nested_array(array $a): bool
    {
        foreach ($a as $v) {
            if (is_array($v)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if an array is associative, i.e. has a string as key
     */
    protected function is_associative_array(array $array): bool
    {
        foreach ($array as $k => $v) {
            if (is_string($k)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Converts array to comma-separated list;
     * Converts boolean value to true', 'false' string
     *
     * @param mixed $value
     */
    private function convert_value($value): string
    {
        // Convert a boolean value in 'true' or 'false' string
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
            // Convert to comma-separated list if array
        } elseif (is_array($value) && $this->is_nested_array($value) === false) {
            return implode(',', $value);
        }
        return (string) $value;
    }
    /**
     * Encode a value for a valid URL
     *
     * @param mixed $value
     */
    protected function encode($value): string
    {
        return urlencode($this->convert_value($value));
    }
    /**
     * Returns the URL with the query string from $params
     * extracting the array keys specified in $keys
     */
    protected function add_query_string(string $url, array $params, array $keys): string
    {
        $query_params = [];
        foreach ($keys as $k) {
            if (isset($params[$k])) {
                $query_params[$k] = $this->convert_value($params[$k]);
            }
        }
        if (empty($query_params)) {
            return $url;
        }
        return $url . '?' . http_build_query($query_params);
    }
    /**
     * Serialize the body using the Content-Type
     *
     * @param mixed $body
     */
    protected function body_serialize($body, string $content_type): string
    {
        if (str_contains($content_type, 'application/x-ndjson') || str_contains($content_type, 'application/vnd.elasticsearch+x-ndjson')) {
            return Nd_Json_Serializer::serialize($body, ['remove_null' => false]);
        }
        if (str_contains($content_type, 'application/json') || str_contains($content_type, 'application/vnd.elasticsearch+json')) {
            return Json_Serializer::serialize($body, ['remove_null' => false]);
        }
        throw new Content_Type_Exception(sprintf('The Content-Type %s is not managed by Elasticsearch serializer', $content_type));
    }
    /**
     * Create a PSR-7 request
     *
     * @param array|string $body
     */
    protected function create_request(string $method, string $url, array $headers, $body = null): Request_Interface
    {
        $request_factory = Psr17factory_Discovery::find_request_factory();
        $stream_factory = Psr17factory_Discovery::find_stream_factory();
        $request = $request_factory->create_request($method, $url);
        // Body request
        if (!empty($body)) {
            if (!isset($headers['Content-Type'])) {
                throw new Content_Type_Exception(sprintf('The Content-Type is missing for %s %s', $method, $url));
            }
            $content = is_string($body) ? $body : $this->body_serialize($body, $headers['Content-Type']);
            $request = $request->with_body($stream_factory->create_stream($content));
        }
        $headers = $this->build_compatibility_headers($headers);
        // Headers
        foreach ($headers as $name => $value) {
            $request = $request->with_header($name, $value);
        }
        return $request;
    }
    /**
     * Build the API compatibility headers
     * transfrom Content-Type and Accept adding vnd.elasticsearch+ and compatible-with
     *
     * @see https://github.com/elastic/elasticsearch-php/pull/1142
     */
    protected function build_compatibility_headers(array $headers): array
    {
        $header_format = Client::$is_searchly ? Client::API_COMPATIBILITY_HEADER_7 : Client::API_COMPATIBILITY_HEADER_8;
        if (isset($headers['Content-Type'])) {
            if (preg_match('/application\/([^,]+)$/', $headers['Content-Type'], $matches)) {
                $headers['Content-Type'] = sprintf($header_format, 'application', $matches[1]);
            }
        }
        if (isset($headers['Accept'])) {
            $values = explode(',', $headers['Accept']);
            foreach ($values as &$value) {
                if (preg_match('/(application|text)\/([^,]+)/', $value, $matches)) {
                    $value = sprintf($header_format, $matches[1], $matches[2]);
                }
            }
            $headers['Accept'] = implode(',', $values);
        }
        return $headers;
    }
    /**
     * Check if the $required parameters are present in $params
     * @throws MissingParameterException
     */
    protected function check_required_parameters(array $required, array $params): void
    {
        foreach ($required as $req) {
            if (!isset($params[$req])) {
                throw new Missing_Parameter_Exception(sprintf('The parameter %s is required', $req));
            }
        }
    }
}