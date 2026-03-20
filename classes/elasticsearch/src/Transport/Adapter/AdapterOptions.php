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

/**
 * The HTTP client adapters supported
 */
final class Adapter_Options
{
    public const HTTP_ADAPTERS = [\Guzzle_Http\Client::class => \Elastic\Elasticsearch\Transport\Adapter\Guzzle::class, \Symfony\Component\Http_Client\Httplug_Client::class => \Elastic\Elasticsearch\Transport\Adapter\Symfony::class, \Symfony\Component\Http_Client\Psr18Client::class => \Elastic\Elasticsearch\Transport\Adapter\Symfony::class];
}