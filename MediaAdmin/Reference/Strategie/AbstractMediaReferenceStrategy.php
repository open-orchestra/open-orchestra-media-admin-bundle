<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategie;

use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use OpenOrchestra\BBcodeBundle\Parser\BBcodeParser;
use OpenOrchestra\Media\BBcode\AbstractMediaCodeDefinition;

/**
 * Class AbstractReferenceStrategy
 */
abstract class AbstractMediaReferenceStrategy
{
    protected $mediaRepository;
    protected $bbcodeParser;

    public function __construct(MediaRepositoryInterface $mediaRepository, BBcodeParser $bbcodeParser)
    {
        $this->mediaRepository = $mediaRepository;
        $this->bbcodeParser = $bbcodeParser;
    }

    /**
     * Recursively extract media ids from elements (bloc, attribute, collection attribute, etc ...)
     *
     * @param array $element
     * @param array $references
     *
     * @return array
     */
    protected function extractMediasFromElement($element, array $references = array())
    {
        if ($this->isMediaAttribute($element)) {
            $references[] = $element['id'];
        } elseif (is_string($element) && $this->hasMediaBBcode($element)) {
            $references = $this->extractMediaFromBBCode($element, $references);
        } elseif (is_array($element)) {
            foreach ($element as $item) {
                $references = $this->extractMediasFromElement($item, $references);
            }
        }

        return $references;
    }

    /**
     * @param string $str
     * @param array  $references
     *
     * @return array
     */
    protected function extractMediaFromBBCode($str, array $references)
    {
        /** @var BBcodeParserInterface $parserBBcode */
        $parsedBBcode = $this->bbcodeParser->parse($str);
        $mediaTags = $parsedBBcode->getElementByTagName(AbstractMediaCodeDefinition::TAG_NAME);
        /** @var BBcodeElementNode $mediaTag */
        foreach ($mediaTags as $mediaTag) {
            $references[] = $mediaTag->getAsText();
        }

        return $references;
    }

    /**
     * @param $str
     *
     * @return boolean
     */
    protected function hasMediaBBcode($str)
    {
        $MediaBBCode = '/\[' . AbstractMediaCodeDefinition::TAG_NAME . '(\=\{.*\})?].*\[\/'
            . AbstractMediaCodeDefinition::TAG_NAME . '\]/m';

        return preg_match($MediaBBCode, $str) === 1;
    }

    /**
     * Check if $attributeValue matches with a media attribute
     *
     * @param mixed $attributeValue
     *
     * @return boolean
     */
    protected function isMediaAttribute($attributeValue)
    {
        return is_array($attributeValue)
            && isset($attributeValue['id'])
            && array_key_exists('format', $attributeValue)
            && $attributeValue['id'] != '';
    }
}
