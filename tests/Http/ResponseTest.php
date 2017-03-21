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

use NilPortugues\Api\Hal\Http\Response\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonResponse()
    {
        $json = \json_encode([]);
        $response = new Response($json);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/hal+json; charset=utf-8'], $response->getHeader('Content-type'));
    }

    public function testXmlResponse()
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<resource>
  <total><![CDATA[2]]></total>
  <embedded>
    <resource>
      <post_id><![CDATA[1]]></post_id>
      <title><![CDATA[post title 1]]></title>
      <body><![CDATA[post body 1]]></body>
      <author_id><![CDATA[4]]></author_id>
      <comments/>
    </resource>
    <resource>
      <post_id><![CDATA[2]]></post_id>
      <title><![CDATA[post title 2]]></title>
      <body><![CDATA[post body 2]]></body>
      <author_id><![CDATA[5]]></author_id>
      <comments/>
    </resource>
  </embedded>
</resource>
XML;
        $response = new Response($xml);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/hal+xml; charset=utf-8'], $response->getHeader('Content-type'));
    }
}
