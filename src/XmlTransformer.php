<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 25/12/15
 * Time: 22:27.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Api\Hal;

use DOMDocument;
use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Api\Mapping\MappingFactory;
use SimpleXMLElement;

/**
 * This Transformer follows the JSON+HAL specification.
 *
 * @link http://stateless.co/hal_specification.html
 */
class XmlTransformer extends JsonTransformer implements HalTransformer
{
    /**
     * @var array
     */
    private $linkKeys = [
        JsonTransformer::LINKS_HREF,
        JsonTransformer::LINKS_HREF_LANG_KEY,
        JsonTransformer::LINKS_TITLE_KEY,
        JsonTransformer::LINKS_TYPE_KEY,
    ];

    /**
     * XmlTransformer constructor.
     *
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        $this->addHalPaginationMapping($mapper);
        parent::__construct($mapper);
    }

    /**
     * @param Mapper $mapper
     */
    protected function addHalPaginationMapping(Mapper $mapper)
    {
        $mappings = $mapper->getClassMap();

        $halPaginationMapping = MappingFactory::fromClass(HalPaginationMapping::class);
        $mappings[ltrim($halPaginationMapping->getClassName(), '\\')] = $halPaginationMapping;

        $mapper->setClassMap($mappings);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function outputStrategy(array &$data)
    {
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><resource></resource>');
        $this->arrayToXml($data, $xmlData);
        if (!empty($data[JsonTransformer::LINKS_KEY][JsonTransformer::LINK_SELF][JsonTransformer::LINKS_HREF])) {
            $xmlData->addAttribute(
                JsonTransformer::LINKS_HREF,
                $data[JsonTransformer::LINKS_KEY][JsonTransformer::LINK_SELF][JsonTransformer::LINKS_HREF]
            );
        }

        $xml = $xmlData->asXML();
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xml);
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;
        $xmlDoc->substituteEntities = false;

        return rtrim(html_entity_decode($xmlDoc->saveXML()), "\n");
    }

    /**
     * Converts an array to XML using SimpleXMLElement.
     *
     * @param array            $data
     * @param SimpleXMLElement $xmlData
     */
    private function arrayToXml(array &$data, SimpleXMLElement $xmlData)
    {
        foreach ($data as $key => $value) {
            $key = ltrim($key, '_');

            if (\is_array($value)) {
                if (\is_numeric($key)) {
                    $key = 'resource';
                }

                if (false === empty($value[JsonTransformer::LINKS_HREF])) {
                    $subnode = $xmlData->addChild('link');
                    $subnode->addAttribute('rel', $key);

                    foreach ($this->linkKeys as $linkKey) {
                        if (!empty($value[$linkKey])) {
                            $subnode->addAttribute($linkKey, $value[$linkKey]);
                        }
                    }
                } else {
                    if (!empty($value[JsonTransformer::LINKS_KEY][JsonTransformer::LINK_SELF][JsonTransformer::LINKS_HREF])) {
                        $subnode = $xmlData->addChild('resource');
                        $subnode->addAttribute(
                            JsonTransformer::LINKS_HREF,
                            $value[JsonTransformer::LINKS_KEY][JsonTransformer::LINK_SELF][JsonTransformer::LINKS_HREF]
                        );

                        if ($key !== 'resource') {
                            $subnode->addAttribute('rel', $key);
                        }
                    } else {
                        $subnode = $xmlData->addChild($key);
                    }
                }

                $this->arrayToXml($value, $subnode);
            } else {
                if ($key !== JsonTransformer::LINKS_HREF) {
                    if ($value === true || $value === false) {
                        $value = ($value) ? 'true' : 'false';
                    }

                    $xmlData->addChild("$key", '<![CDATA['.html_entity_decode($value).']]>');
                }
            }
        }
    }
}
