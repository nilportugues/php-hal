<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/14/15
 * Time: 8:48 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\HalJson\Helpers;

use NilPortugues\Api\HalJson\HalJsonTransformer;

final class AttributeFormatterHelper
{
    /**
     * Simplifies the data structure by removing an array level if data is scalar and has one element in array.
     *
     * @param array $array
     */
    public static function flattenObjectsWithSingleKeyScalars(array &$array)
    {
        if (1 === \count($array) && \is_scalar(\end($array))) {
            $array = \array_pop($array);
        }

        if (\is_array($array)) {
            self::loopScalarValues($array, 'flattenObjectsWithSingleKeyScalars');
        }
    }

    /**
     * @param array  $array
     * @param string $method
     */
    private static function loopScalarValues(array &$array, $method)
    {
        foreach ($array as $propertyName => &$value) {
            if (\is_array($value) && HalJsonTransformer::LINKS_KEY !== $propertyName) {
                self::$method($value);
            }
        }
    }
}
