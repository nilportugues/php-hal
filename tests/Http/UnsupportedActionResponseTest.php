<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/1/15
 * Time: 12:29 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Api\Hal\Http\Message;

use NilPortugues\Api\Hal\Http\Response\UnsupportedActionResponse;

class UnsupportedActionResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $json = \json_encode([]);
        $response = new UnsupportedActionResponse($json);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals(['application/hal+json; charset=utf-8'], $response->getHeader('Content-type'));
    }
}
