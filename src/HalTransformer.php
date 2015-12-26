<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 26/12/15
 * Time: 10:37.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\Hal;

use NilPortugues\Serializer\Strategy\StrategyInterface;

interface HalTransformer extends StrategyInterface
{
    /**
     * @param array $value
     *
     * @return string
     */
    public function serialize($value);
}
