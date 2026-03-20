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
namespace Elastic\Elasticsearch\Transport\Adapter;

use Elastic\Elasticsearch\Transport\Request_Options;
use Guzzle_Http\Request_Options as GuzzleOptions;
use Psr\Http\Client\Client_Interface;
class Guzzle implements Adapter_Interface
{
    public function set_config(Client_Interface $client, array $config, array $client_options): Client_Interface
    {
        $guzzle_config = [];
        foreach ($config as $key => $value) {
            switch ($key) {
                case Request_Options::SSL_CERT:
                    $guzzle_config[Guzzle_Options::CERT] = $value;
                    break;
                case Request_Options::SSL_KEY:
                    $guzzle_config[Guzzle_Options::SSL_KEY] = $value;
                    break;
                case Request_Options::SSL_VERIFY:
                    $guzzle_config[Guzzle_Options::VERIFY] = $value;
                    break;
                case Request_Options::SSL_CA:
                    $guzzle_config[Guzzle_Options::VERIFY] = $value;
            }
        }
        $class = $client::class;
        return new $class(array_merge($client_options, $guzzle_config));
    }
}