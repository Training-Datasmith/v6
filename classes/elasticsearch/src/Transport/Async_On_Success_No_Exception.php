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
namespace Elastic\Elasticsearch\Transport;

use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Transport\Async\On_Success_Interface;
use Psr\Http\Message\Response_Interface;
class Async_On_Success_No_Exception implements On_Success_Interface
{
    public function success(Response_Interface $response, int $count): Elasticsearch
    {
        $result = new Elasticsearch();
        $result->set_response($response, false);
        return $result;
    }
}