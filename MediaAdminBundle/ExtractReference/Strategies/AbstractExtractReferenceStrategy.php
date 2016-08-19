<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies;

use OpenOrchestra\BaseBundle\Manager\TagManager;
use OpenOrchestra\BBcodeBundle\ElementNode\BBcodeElementNode;
use OpenOrchestra\BBcodeBundle\Parser\BBcodeParserInterface;
use OpenOrchestra\Media\BBcode\AbstractMediaCodeDefinition;
use OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceInterface;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;

/**
 * Class AbstractExtractReferenceStrategy
 */
abstract class AbstractExtractReferenceStrategy implements ExtractReferenceInterface
{
    protected $bbcodeParser;
    protected $tagManager;

    /**
     * @param BBcodeParserInterface $bbCoderParser
     * @param TagManager            $tagManager
     */
    public function __construct(BBcodeParserInterface $bbCoderParser, TagManager $tagManager)
    {
        $this->bbcodeParser = $bbCoderParser;
        $this->tagManager = $tagManager;
    }

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return bool
     */
    abstract public function support(StatusableInterface $statusableElement);

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return array
     */
    abstract public function extractReference(StatusableInterface $statusableElement);

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * Get Reference pattern for $statusableElement
     *
     * @param string $statusableElementId
     *
     * return string
     */
    abstract public function getReferencePattern($statusableElementId);

    /**
     * Format a reference
     *
     * @param string $index
     * @param string $statusableElementId
     *
     * @return string
     */
    abstract protected function formatReference($index, $statusableElementId);

    /**
     * Recursively extract media references from elements (bloc, attribute, collection attribute, etc ...)
     *
     * @param array  $element
     * @param string $index
     * @param string $statusableElementId
     * @param array  $references
     *
     * @return array
     */
    protected function extractMedia($index, $element, $statusableElementId, $references = array())
    {
        if ($this->isMediaAttribute($element)) {
            $references[$element['id']][] = $this->formatReference($index, $statusableElementId);
        } elseif (is_string($element) && $this->hasBBcodeMedia($element)) {
            $references = $this->extractMediaBBCode($element, $index, $statusableElementId, $references);
        } elseif (is_array($element)) {
            foreach ($element as $item) {
                $references = $this->extractMedia($index, $item, $statusableElementId , $references);
            }
        }

        return $references;
    }

    /**
     * @param string $str
     * @param string $index
     * @param string $statusableElementId
     * @param array  $references
     *
     * @return array
     */
    protected function extractMediaBBCode($str, $index, $statusableElementId, array $references)
    {
        /** @var BBcodeParserInterface $parserBBcode */
        $parserBBcode = $this->bbcodeParser->parse($str);
        $mediaTags = $parserBBcode->getElementByTagName(AbstractMediaCodeDefinition::TAG_NAME);
        /** @var BBcodeElementNode $mediaTag */
        foreach ($mediaTags as $mediaTag) {
            $references[$mediaTag->getAsText()][] = $this->formatReference($index, $statusableElementId);
        }

        return $references;
    }

    /**
     * @param $str
     *
     * @return bool
     */
    protected function hasBBcodeMedia($str)
    {
        $BBCodeMedia = '/\['.AbstractMediaCodeDefinition::TAG_NAME.'(\=\{.*\})?].*\[\/'.AbstractMediaCodeDefinition::TAG_NAME.'\]/m';

        return preg_match($BBCodeMedia, $str) === 1;
    }

    /**
     * Check if $attributeValue matches with a media attribute
     *
     * @param mixed $attributeValue
     *
     * @return bool
     */
    protected function isMediaAttribute($attributeValue)
    {
        return is_array($attributeValue)
            && isset($attributeValue['id'])
            && array_key_exists('format', $attributeValue)
            && $attributeValue['id'] != '';
    }
}
