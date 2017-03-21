<?php

namespace NilPortugues\Api\Hal;

use NilPortugues\Api\Mappings\HalMapping;

class HalPaginationMapping implements HalMapping
{
    /**
     * Returns a string with the full class name, including namespace.
     *
     * @return string
     */
    public function getClass()
    {
        return HalPagination::class;
    }

    /**
     * Returns a string representing the resource name as it will be shown after the mapping.
     *
     * @return string
     */
    public function getAlias()
    {
        return '';
    }

    /**
     * Returns an array of properties that will be renamed.
     * Key is current property from the class. Value is the property's alias name.
     *
     * @return array
     */
    public function getAliasedProperties()
    {
        return [];
    }

    /**
     * List of properties in the class that will be ignored by the mapping.
     *
     * @return array
     */
    public function getHideProperties()
    {
        return [];
    }

    /**
     * Returns an array of properties that are used as an ID value.
     *
     * @return array
     */
    public function getIdProperties()
    {
        return ['total']; //Any field does the trick for this special case. ;)
    }

    /**
     * Returns a list of URLs. This urls must have placeholders to be replaced with the getIdProperties() values.
     *
     * @return array
     */
    public function getUrls()
    {
        return [
            'self' => 'http://example.com', //we won't be using it at all.
        ];
    }

    /**
     * Returns an array of curies.
     *
     * @return array
     */
    public function getCuries()
    {
        return [];
    }
}
