<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 7/28/15
 * Time: 1:20 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\Hal\Http\Response;

abstract class AbstractResponse extends \NilPortugues\Api\Http\Message\AbstractResponse
{
    /**
     * @var array
     */
    protected $headers = [
        'Content-type' => 'application/hal+json; charset=utf-8',
        'Cache-Control' => 'private, max-age=0, must-revalidate',
    ];

    /**
     * @param string $body
     */
    public function __construct($body)
    {
        if (is_string($body) && substr($body, 0, strlen('<?xml')) === '<?xml') {
            $this->headers['Content-type'] = 'application/hal+xml; charset=utf-8';
        }

        $this->response = self::instance($body, $this->httpCode, $this->headers);
    }
}
