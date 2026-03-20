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
use Elastic\Elasticsearch\Exception\Product_Check_Exception;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Psr\Http\Message\Response_Interface;
trait Product_Check_Trait
{
    /**
     * Check if the response comes from Elasticsearch server
     */
    private function product_check(Response_Interface $response): void
    {
        // Skip product check for Searchly (ES fork)
        if (Client::$is_searchly) {
            return;
        }
        $status_code = $response->get_status_code();
        if ($status_code >= 200 && $status_code < 300) {
            $product = $response->get_header_line(Elasticsearch::HEADER_CHECK);
            if (empty($product) || $product !== Elasticsearch::PRODUCT_NAME) {
                throw new Product_Check_Exception('The client noticed that the server is not Elasticsearch and we do not support this unknown product');
            }
        }
    }
}