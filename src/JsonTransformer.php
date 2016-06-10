<?php

namespace NilPortugues\Api\Hal;

use NilPortugues\Api\Hal\Helpers\AttributeFormatterHelper;
use NilPortugues\Api\Hal\Helpers\CuriesHelper;
use NilPortugues\Api\Transformer\Helpers\RecursiveDeleteHelper;
use NilPortugues\Api\Transformer\Helpers\RecursiveFormatterHelper;
use NilPortugues\Api\Transformer\Helpers\RecursiveRenamerHelper;
use NilPortugues\Api\Transformer\Transformer;
use NilPortugues\Serializer\Serializer;

/**
 * This Transformer follows the JSON+HAL specification.
 *
 * @link http://stateless.co/hal_specification.html
 */
class JsonTransformer extends Transformer implements HalTransformer
{
    const EMBEDDED_KEY = '_embedded';
    const META_KEY = '_meta';

    const LINKS_KEY = '_links';
    const LINKS_TEMPLATED_KEY = 'templated';
    const LINKS_DEPRECATION_KEY = 'deprecation';
    const LINKS_TYPE_KEY = 'type';
    const LINKS_NAME_KEY = 'name';
    const LINKS_PROFILE_KEY = 'profile';
    const LINKS_TITLE_KEY = 'title';
    const LINKS_HREF_LANG_KEY = 'hreflang';
    const LINKS_HREF = 'href';

    const MEDIA_PROFILE_KEY = 'profile';

    const SELF_LINK = 'self';
    const FIRST_LINK = 'first';
    const LAST_LINK = 'last';
    const PREV_LINK = 'prev';
    const NEXT_LINK = 'next';
    const LINKS_CURIES = 'curies';

    /**
     * @var array
     */
    protected $curies = [];

    /**
     * @param array $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $this->noMappingGuard();

        if (\is_array($value) && !empty($value[Serializer::MAP_TYPE])) {
            $data = ['total' => 0];
            unset($value[Serializer::MAP_TYPE]);

            foreach ($value[Serializer::SCALAR_VALUE] as $v) {
                $data[self::EMBEDDED_KEY][] = $this->serializeObject($v);
            }
            $data['total'] = count($data[self::EMBEDDED_KEY]);
        } else {
            $data = $this->serializeObject($value);
        }

        return $this->outputStrategy($data);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function outputStrategy(array &$data)
    {
        return \json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array $value
     *
     * @return array
     */
    protected function serializeObject(array $value)
    {
        $value = $this->preSerialization($value);
        $data = $this->serialization($value);

        return $this->postSerialization($data);
    }

    /**
     * @param array $value
     *
     * @return array
     */
    protected function preSerialization(array $value)
    {
        /** @var \NilPortugues\Api\Mapping\Mapping $mapping */
        foreach ($this->mappings as $class => $mapping) {
            RecursiveDeleteHelper::deleteProperties($this->mappings, $value, $class);
            RecursiveRenamerHelper::renameKeyValue($this->mappings, $value, $class);
        }

        return $value;
    }

    /**
     * @param array $data
     *d
     *
     * @return array
     */
    protected function serialization(array $data)
    {
        $this->setEmbeddedResources($data);
        $this->setResponseLinks($data);

        return $data;
    }

    /**
     * @param array $data
     */
    protected function setEmbeddedResources(array &$data)
    {
        foreach ($data as $propertyName => &$value) {
            if (\is_array($value)) {
                $this->setEmbeddedForResource($data, $value, $propertyName);
                $this->setEmbeddedForResourceArray($data, $value, $propertyName);
            }
        }
    }

    /**
     * @param array  $data
     * @param array  $value
     * @param string $propertyName
     */
    protected function setEmbeddedForResource(array &$data, array &$value, $propertyName)
    {
        if (!empty($value[Serializer::CLASS_IDENTIFIER_KEY])) {
            $type = $value[Serializer::CLASS_IDENTIFIER_KEY];
            if (\is_scalar($type) && !empty($this->mappings[$type])) {
                $idProperties = $this->mappings[$type]->getIdProperties();
                CuriesHelper::addCurieForResource($this->mappings, $this->curies, $type);

                if (false === \in_array($propertyName, $idProperties)) {
                    $data[self::EMBEDDED_KEY][$propertyName] = $value;

                    list($idValues, $idProperties) = RecursiveFormatterHelper::getIdPropertyAndValues(
                        $this->mappings,
                        $value,
                        $type
                    );

                    $this->addEmbeddedResourceLinks($data, $propertyName, $idProperties, $idValues, $type);
                    $this->addEmbeddedResourceAdditionalLinks($data, $value, $propertyName, $type);
                    $this->addEmbeddedResourceLinkToLinks($data, $propertyName, $idProperties, $idValues, $type);

                    unset($data[$propertyName]);
                }
            }
        } else {
            $data[$propertyName] = $value;
        }
    }

    /**
     * @param array  $data
     * @param string $propertyName
     * @param array  $idProperties
     * @param array  $idValues
     * @param string $type
     */
    protected function addEmbeddedResourceLinks(
        array &$data,
        $propertyName,
        array &$idProperties,
        array &$idValues,
        $type
    ) {
        $href = self::buildUrl(
            $this->mappings,
            $idProperties,
            $idValues,
            $this->mappings[$type]->getResourceUrl(),
            $type
        );

        if ($href != $this->mappings[$type]->getResourceUrl()) {
            $data[self::EMBEDDED_KEY][$propertyName][self::LINKS_KEY][self::SELF_LINK][self::LINKS_HREF] = $href;
        }
    }

    /**
     * @param array  $data
     * @param array  $value
     * @param string $propertyName
     * @param string $type
     */
    protected function addEmbeddedResourceAdditionalLinks(array &$data, array &$value, $propertyName, $type)
    {
        $links = [];

        if (!empty($data[self::EMBEDDED_KEY][$propertyName][self::LINKS_KEY])) {
            $links = $data[self::EMBEDDED_KEY][$propertyName][self::LINKS_KEY];
        }

        $data[self::EMBEDDED_KEY][$propertyName][self::LINKS_KEY] = \array_merge(
            $links,
            $this->addHrefToLinks($this->getResponseAdditionalLinks($value, $type))
        );
    }

    /**
     * @param array  $copy
     * @param string $type
     *
     * @return array
     */
    protected function getResponseAdditionalLinks(array $copy, $type)
    {
        $otherUrls = $this->mappings[$type]->getUrls();
        list($idValues, $idProperties) = RecursiveFormatterHelper::getIdPropertyAndValues(
            $this->mappings,
            $copy,
            $type
        );

        $newOtherUrls = $otherUrls;
        foreach ($newOtherUrls as &$url) {
            $url = self::buildUrl($this->mappings, $idProperties, $idValues, $url, $type);
        }

        if ($newOtherUrls == $otherUrls) {
            return [];
        }

        $otherUrls = $newOtherUrls;
        foreach ($otherUrls as $propertyName => $value) {
            $curieName = $this->getPropertyNameWithCurie($type, $propertyName);
            $otherUrls[$curieName] = $value;

            if ($propertyName !== $curieName) {
                unset($otherUrls[$propertyName]);
            }
        }

        return $otherUrls;
    }

    /**
     * @param string $type
     * @param string $propertyName
     *
     * @return string
     */
    protected function getPropertyNameWithCurie($type, $propertyName)
    {
        $curie = $this->mappings[$type]->getCuries();
        if (!empty($curie)) {
            $propertyName = sprintf(
                '%s:%s',
                $curie['name'],
                self::camelCaseToUnderscore($propertyName)
            );
        }

        return $propertyName;
    }

    /**
     * @param array  $data
     * @param string $propertyName
     * @param array  $idProperties
     * @param array  $idValues
     * @param string $type
     */
    protected function addEmbeddedResourceLinkToLinks(
        array &$data,
        $propertyName,
        array &$idProperties,
        array &$idValues,
        $type
    ) {
        $href = self::buildUrl(
            $this->mappings,
            $idProperties,
            $idValues,
            $this->mappings[$type]->getResourceUrl(),
            $type
        );

        if ($href != $this->mappings[$type]->getResourceUrl()) {
            $data[self::LINKS_KEY][$this->getPropertyNameWithCurie($type, $propertyName)][self::LINKS_HREF] = $href;
        }
    }

    /**
     * @param array  $data
     * @param array  $value
     * @param string $propertyName
     */
    protected function setEmbeddedForResourceArray(array &$data, array &$value, $propertyName)
    {
        if (!empty($value[Serializer::MAP_TYPE])) {
            foreach ($value as &$arrayValue) {
                if (\is_array($arrayValue)) {
                    $this->setEmbeddedArrayValue($data, $propertyName, $arrayValue);
                }
            }
        }
    }

    /**
     * @param array  $data
     * @param string $propertyName
     * @param array  $arrayValue
     */
    protected function setEmbeddedArrayValue(array &$data, $propertyName, array &$arrayValue)
    {
        foreach ($arrayValue as $inArrayProperty => &$inArrayValue) {
            if ($this->isResourceInArray($inArrayValue)) {
                $this->setEmbeddedResources($inArrayValue);

                $data[self::EMBEDDED_KEY][$propertyName][$inArrayProperty] = $inArrayValue;
                $type = $inArrayValue[Serializer::CLASS_IDENTIFIER_KEY];

                CuriesHelper::addCurieForResource($this->mappings, $this->curies, $type);
                $this->addArrayValueResourceToEmbedded($data, $propertyName, $type, $inArrayProperty, $inArrayValue);

                unset($data[$propertyName]);
            }
        }
    }

    /**
     * @param mixed $inArrayValue
     *
     * @return bool
     */
    protected function isResourceInArray($inArrayValue)
    {
        return \is_array($inArrayValue) && !empty($inArrayValue[Serializer::CLASS_IDENTIFIER_KEY]);
    }

    /**
     * @param array  $data
     * @param string $propertyName
     * @param string $type
     * @param string $inArrayProperty
     * @param array  $inArrayValue
     */
    protected function addArrayValueResourceToEmbedded(
        array &$data,
        $propertyName,
        $type,
        $inArrayProperty,
        array &$inArrayValue
    ) {
        list($idValues, $idProperties) = RecursiveFormatterHelper::getIdPropertyAndValues(
            $this->mappings,
            $inArrayValue,
            $type
        );

        $href = self::buildUrl(
            $this->mappings,
            $idProperties,
            $idValues,
            $this->mappings[$type]->getResourceUrl(),
            $type
        );

        if ($href != $this->mappings[$type]->getResourceUrl()) {
            $data[self::EMBEDDED_KEY][$propertyName][$inArrayProperty][self::LINKS_KEY][self::SELF_LINK][self::LINKS_HREF] = $href;
        }
    }

    /**
     * @param array $data
     */
    protected function setResponseLinks(array &$data)
    {
        if (!empty($data[Serializer::CLASS_IDENTIFIER_KEY])) {
            $data[self::LINKS_KEY] = \array_merge(
                CuriesHelper::buildCuries($this->curies),
                $this->addHrefToLinks($this->buildLinks()),
                (!empty($data[self::LINKS_KEY])) ? $data[self::LINKS_KEY] : [],
                $this->addHrefToLinks($this->getResponseAdditionalLinks($data, $data[Serializer::CLASS_IDENTIFIER_KEY]))
            );

            $data[self::LINKS_KEY] = \array_filter($data[self::LINKS_KEY]);

            if (empty($data[self::LINKS_KEY])) {
                unset($data[self::LINKS_KEY]);
            }
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function postSerialization(array &$data)
    {
        RecursiveDeleteHelper::deleteKeys($data, [Serializer::CLASS_IDENTIFIER_KEY]);
        RecursiveDeleteHelper::deleteKeys($data, [Serializer::MAP_TYPE]);
        RecursiveFormatterHelper::formatScalarValues($data);
        AttributeFormatterHelper::flattenObjectsWithSingleKeyScalars($data);
        $this->recursiveSetKeysToUnderScore($data);
        $this->setResponseMeta($data);

        return $data;
    }

    /**
     * @param array $response
     */
    protected function setResponseMeta(array &$response)
    {
        if (!empty($this->meta)) {
            $response[self::META_KEY] = $this->meta;
        }
    }

    /**
     * @param \NilPortugues\Api\Mapping\Mapping[] $mappings
     * @param                                     $idProperties
     * @param                                     $idValues
     * @param                                     $url
     * @param                                     $type
     *
     * @return mixed
     */
    protected static function buildUrl(array &$mappings, $idProperties, $idValues, $url, $type)
    {
        $outputUrl = \str_replace($idProperties, $idValues, $url);
        if ($outputUrl !== $url) {
            return $outputUrl;
        }

        $outputUrl = self::secondPassBuildUrl([$mappings[$type]->getClassAlias()], $idValues, $url);

        if ($outputUrl !== $url) {
            return $outputUrl;
        }

        $className = $mappings[$type]->getClassName();
        $className = \explode('\\', $className);
        $className = \array_pop($className);

        $outputUrl = self::secondPassBuildUrl([$className], $idValues, $url);
        if ($outputUrl !== $url) {
            return $outputUrl;
        }

        return $url;
    }

    /**
     * @param $idPropertyName
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function secondPassBuildUrl($idPropertyName, $idValues, $url)
    {
        if (!empty($idPropertyName)) {
            $outputUrl = self::toCamelCase($idPropertyName, $idValues, $url);
            if ($url !== $outputUrl) {
                return $outputUrl;
            }

            $outputUrl = self::toLowerFirstCamelCase($idPropertyName, $idValues, $url);
            if ($url !== $outputUrl) {
                return $outputUrl;
            }

            $outputUrl = self::toUnderScore($idPropertyName, $idValues, $url);
            if ($url !== $outputUrl) {
                return $outputUrl;
            }
        }

        return $url;
    }

    /**
     * @param $original
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function toCamelCase($original, $idValues, $url)
    {
        foreach ($original as &$o) {
            $o = '{'.self::underscoreToCamelCase(self::camelCaseToUnderscore($o)).'}';
        }

        return \str_replace($original, $idValues, $url);
    }

    /**
     * @param $original
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function toLowerFirstCamelCase($original, $idValues, $url)
    {
        foreach ($original as &$o) {
            $o = self::underscoreToCamelCase(self::camelCaseToUnderscore($o));
            $o[0] = \strtolower($o[0]);
            $o = '{'.$o.'}';
        }

        return \str_replace($original, $idValues, $url);
    }

    /**
     * @param $original
     * @param $idValues
     * @param $url
     *
     * @return mixed
     */
    protected static function toUnderScore($original, $idValues, $url)
    {
        foreach ($original as &$o) {
            $o = '{'.self::camelCaseToUnderscore($o).'}';
        }

        return \str_replace($original, $idValues, $url);
    }

    /**
     * Transforms a given string from camelCase to under_score style.
     *
     * @param string $camel
     * @param string $splitter
     *
     * @return string
     */
    protected static function camelCaseToUnderscore($camel, $splitter = '_')
    {
        $camel = \preg_replace(
            '/(?!^)[[:upper:]][[:lower:]]/',
            '$0',
            \preg_replace('/(?!^)[[:upper:]]+/', $splitter.'$0', $camel)
        );

        return \strtolower($camel);
    }

    /**
     * Converts a underscore string to camelCase.
     *
     * @param string $string
     *
     * @return string
     */
    protected static function underscoreToCamelCase($string)
    {
        return \str_replace(' ', '', \ucwords(\strtolower(\str_replace(['_', '-'], ' ', $string))));
    }
}
