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
namespace Elastic\Elasticsearch\Response;

use ArrayAccess;
use Elastic\Elasticsearch\Exception\Array_Access_Exception;
use Elastic\Elasticsearch\Exception\Client_Response_Exception;
use Elastic\Elasticsearch\Exception\Server_Response_Exception;
use Elastic\Elasticsearch\Traits\Message_Response_Trait;
use Elastic\Elasticsearch\Traits\Product_Check_Trait;
use Elastic\Transport\Exception\Unknown_Content_Type_Exception;
use Elastic\Transport\Serializer\Csv_Serializer;
use Elastic\Transport\Serializer\Json_Serializer;
use Elastic\Transport\Serializer\Nd_Json_Serializer;
use Elastic\Transport\Serializer\Xml_Serializer;
use Psr\Http\Message\Response_Interface;
/**
 * Wraps a PSR-7 ResponseInterface offering helpers to deserialize the body response
 */
class Elasticsearch implements Elasticsearch_Interface, Response_Interface, ArrayAccess, \Stringable
{
    use Product_Check_Trait;
    use Message_Response_Trait;
    public const HEADER_CHECK = 'X-Elastic-Product';
    public const PRODUCT_NAME = 'Elasticsearch';
    protected array $as_array;
    protected object $as_object;
    protected string $as_string;
    /**
     * The PSR-7 response
     */
    protected Response_Interface $response;
    /**
     * Enable or disable the response Exception
     */
    protected bool $response_exception;
    /**
     * @throws ClientResponseException if status code 4xx
     * @throws ServerResponseException if status code 5xx
     */
    public function set_response(Response_Interface $response, bool $throw_exception = true): void
    {
        $this->product_check($response);
        $this->response = $response;
        $status = $response->get_status_code();
        if ($throw_exception && $status > 399 && $status < 500) {
            $error = new Client_Response_Exception(sprintf('%s %s: %s', $status, $response->get_reason_phrase(), (string) $response->get_body()), $status);
            throw $error->set_response($response);
        }
        if ($throw_exception && $status > 499 && $status < 600) {
            $error = new Server_Response_Exception(sprintf('%s %s: %s', $status, $response->get_reason_phrase(), (string) $response->get_body()), $status);
            throw $error->set_response($response);
        }
    }
    /**
     * Return true if status code is 2xx
     */
    public function as_bool(): bool
    {
        return $this->response->get_status_code() >= 200 && $this->response->get_status_code() < 300;
    }
    /**
     * Converts the body content to array, if possible.
     * Otherwise, it throws an UnknownContentTypeException
     * if Content-Type is not specified or unknown.
     *
     * @throws UnknownContentTypeException
     */
    public function as_array(): array
    {
        if (isset($this->as_array)) {
            return $this->as_array;
        }
        if (!$this->response->has_header('Content-Type')) {
            throw new Unknown_Content_Type_Exception('No Content-Type specified in the response');
        }
        $content_type = $this->response->get_header_line('Content-Type');
        if (str_contains($content_type, 'application/json') || str_contains($content_type, 'application/vnd.elasticsearch+json')) {
            $this->as_array = Json_Serializer::unserialize($this->as_string());
            return $this->as_array;
        }
        if (str_contains($content_type, 'application/x-ndjson') || str_contains($content_type, 'application/vnd.elasticsearch+x-ndjson')) {
            $this->as_array = Nd_Json_Serializer::unserialize($this->as_string());
            return $this->as_array;
        }
        if (str_contains($content_type, 'text/csv')) {
            $this->as_array = Csv_Serializer::unserialize($this->as_string());
            return $this->as_array;
        }
        throw new Unknown_Content_Type_Exception(sprintf('Cannot deserialize the reponse as array with Content-Type: %s', $content_type));
    }
    /**
     * Converts the body content to object, if possible.
     * Otherwise, it throws an UnknownContentTypeException
     * if Content-Type is not specified or unknown.
     *
     * @throws UnknownContentTypeException
     */
    public function as_object(): object
    {
        if (isset($this->as_object)) {
            return $this->as_object;
        }
        $content_type = $this->response->get_header_line('Content-Type');
        if (str_contains($content_type, 'application/json') || str_contains($content_type, 'application/vnd.elasticsearch+json')) {
            $this->as_object = Json_Serializer::unserialize($this->as_string(), ['type' => 'object']);
            return $this->as_object;
        }
        if (str_contains($content_type, 'application/x-ndjson') || str_contains($content_type, 'application/vnd.elasticsearch+x-ndjson')) {
            $this->as_object = Nd_Json_Serializer::unserialize($this->as_string(), ['type' => 'object']);
            return $this->as_object;
        }
        if (str_contains($content_type, 'text/xml') || str_contains($content_type, 'application/xml')) {
            $this->as_object = Xml_Serializer::unserialize($this->as_string());
            return $this->as_object;
        }
        throw new Unknown_Content_Type_Exception(sprintf('Cannot deserialize the reponse as object with Content-Type: %s', $content_type));
    }
    /**
     * Converts the body content to string
     */
    public function as_string(): string
    {
        if (empty($this->as_string)) {
            $this->as_string = (string) $this->response->get_body();
        }
        return $this->as_string;
    }
    /**
     * Converts the body content to string
     */
    public function __toString(): string
    {
        return $this->as_string();
    }
    /**
     * Access the body content as object properties
     *
     * @see https://www.php.net/manual/en/language.oop5.overloading.php#object.get
     */
    public function __get(string $name): mixed
    {
        return $this->as_object()->{$name} ?? null;
    }
    /**
     * ArrayAccess interface
     *
     * @see https://www.php.net/manual/en/class.arrayaccess.php
     */
    public function offsetExists($offset): bool
    {
        return isset($this->as_array()[$offset]);
    }
    /**
     * ArrayAccess interface
     *
     * @see https://www.php.net/manual/en/class.arrayaccess.php
     */
    #[\Return_Type_Will_Change]
    public function offsetGet($offset)
    {
        return $this->as_array()[$offset];
    }
    /**
     * ArrayAccess interface
     *
     * @see https://www.php.net/manual/en/class.arrayaccess.php
     */
    public function offsetSet($offset, $value): void
    {
        throw new Array_Access_Exception('The array is reading only');
    }
    /**
     * ArrayAccess interface
     *
     * @see https://www.php.net/manual/en/class.arrayaccess.php
     */
    public function offsetUnset($offset): void
    {
        throw new Array_Access_Exception('The array is reading only');
    }
}