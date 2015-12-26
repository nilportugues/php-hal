<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/14/15
 * Time: 9:14 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\Hal\Helpers;

use NilPortugues\Api\Hal\JsonTransformer;

final class CuriesHelper
{
    /**
     * @param array $curies
     *
     * @return array
     */
    public static function buildCuries(array $curies)
    {
        $curiesArray = [];
        $curies = (array) \array_filter($curies);

        if (!empty($curies)) {
            $curiesArray = [JsonTransformer::LINKS_CURIES => \array_values($curies)];

            foreach ($curiesArray[JsonTransformer::LINKS_CURIES] as &$value) {
                $value[JsonTransformer::LINKS_TEMPLATED_KEY] = true;
            }
        }

        return (!empty($curiesArray)) ? $curiesArray : [];
    }

    /**
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param array                               $curies
     * @param string                              $type
     */
    public static function addCurieForResource(array &$mappings, array &$curies, $type)
    {
        $curie = $mappings[$type]->getCuries();
        if (!empty($curie['name'])) {
            $curies[$curie['name']] = $curie;
        }
    }
}
