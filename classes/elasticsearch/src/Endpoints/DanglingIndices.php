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
namespace Elastic\Elasticsearch\Endpoints;

use Elastic\Elasticsearch\Exception\Client_Response_Exception;
use Elastic\Elasticsearch\Exception\Missing_Parameter_Exception;
use Elastic\Elasticsearch\Exception\Server_Response_Exception;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Transport\Exception\No_Node_Available_Exception;
use Http\Promise\Promise;
/**
 * @generated This file is generated, please do not edit
 */
class Dangling_Indices extends Abstract_Endpoint
{
    /**
     * Deletes the specified dangling index
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/modules-gateway-dangling-indices.html
     *
     * @param array{
     *     index_uuid: string, // (REQUIRED) The UUID of the dangling index
     *     accept_data_loss: boolean, // Must be set to true in order to delete the dangling index
     *     timeout: time, // Explicit operation timeout
     *     master_timeout: time, // Specify timeout for connection to master
     *     pretty: boolean, // Pretty format the returned JSON response. (DEFAULT: false)
     *     human: boolean, // Return human readable values for statistics. (DEFAULT: true)
     *     error_trace: boolean, // Include the stack trace of returned errors. (DEFAULT: false)
     *     source: string, // The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     *     filter_path: list, // A comma-separated list of filters used to reduce the response.
     * } $params
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException if the status code of response is 4xx
     * @throws ServerResponseException if the status code of response is 5xx
     *
     * @return Elasticsearch|Promise
     */
    public function delete_dangling_index(array $params = [])
    {
        $this->check_required_parameters(['index_uuid'], $params);
        $url = '/_dangling/' . $this->encode($params['index_uuid']);
        $method = 'DELETE';
        $url = $this->add_query_string($url, $params, ['accept_data_loss', 'timeout', 'master_timeout', 'pretty', 'human', 'error_trace', 'source', 'filter_path']);
        $headers = ['Accept' => 'application/json'];
        return $this->client->send_request($this->create_request($method, $url, $headers, $params['body'] ?? null));
    }
    /**
     * Imports the specified dangling index
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/modules-gateway-dangling-indices.html
     *
     * @param array{
     *     index_uuid: string, // (REQUIRED) The UUID of the dangling index
     *     accept_data_loss: boolean, // Must be set to true in order to import the dangling index
     *     timeout: time, // Explicit operation timeout
     *     master_timeout: time, // Specify timeout for connection to master
     *     pretty: boolean, // Pretty format the returned JSON response. (DEFAULT: false)
     *     human: boolean, // Return human readable values for statistics. (DEFAULT: true)
     *     error_trace: boolean, // Include the stack trace of returned errors. (DEFAULT: false)
     *     source: string, // The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     *     filter_path: list, // A comma-separated list of filters used to reduce the response.
     * } $params
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException if the status code of response is 4xx
     * @throws ServerResponseException if the status code of response is 5xx
     *
     * @return Elasticsearch|Promise
     */
    public function import_dangling_index(array $params = [])
    {
        $this->check_required_parameters(['index_uuid'], $params);
        $url = '/_dangling/' . $this->encode($params['index_uuid']);
        $method = 'POST';
        $url = $this->add_query_string($url, $params, ['accept_data_loss', 'timeout', 'master_timeout', 'pretty', 'human', 'error_trace', 'source', 'filter_path']);
        $headers = ['Accept' => 'application/json'];
        return $this->client->send_request($this->create_request($method, $url, $headers, $params['body'] ?? null));
    }
    /**
     * Returns all dangling indices.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/modules-gateway-dangling-indices.html
     *
     * @param array{
     *     pretty: boolean, // Pretty format the returned JSON response. (DEFAULT: false)
     *     human: boolean, // Return human readable values for statistics. (DEFAULT: true)
     *     error_trace: boolean, // Include the stack trace of returned errors. (DEFAULT: false)
     *     source: string, // The URL-encoded request definition. Useful for libraries that do not accept a request body for non-POST requests.
     *     filter_path: list, // A comma-separated list of filters used to reduce the response.
     * } $params
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException if the status code of response is 4xx
     * @throws ServerResponseException if the status code of response is 5xx
     *
     * @return Elasticsearch|Promise
     */
    public function list_dangling_indices(array $params = [])
    {
        $url = '/_dangling';
        $method = 'GET';
        $url = $this->add_query_string($url, $params, ['pretty', 'human', 'error_trace', 'source', 'filter_path']);
        $headers = ['Accept' => 'application/json'];
        return $this->client->send_request($this->create_request($method, $url, $headers, $params['body'] ?? null));
    }
}