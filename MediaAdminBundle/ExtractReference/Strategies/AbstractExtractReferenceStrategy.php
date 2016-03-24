<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies;

use OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceInterface;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;

/**
 * Class AbstractExtractReferenceStrategy
 */
abstract class AbstractExtractReferenceStrategy implements ExtractReferenceInterface
{
    /**
     * @param StatusableInterface $statusableElement
     *
     * @return bool
     */
    abstract public function support(StatusableInterface $statusableElement);

    /**
     * @param StatusableInterface|NodeInterface $statusableElement
     *
     * @return array
     */
    abstract public function extractReference(StatusableInterface $statusableElement);

    /**
     * @return string
     */
    abstract public function getName();

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

        } elseif (is_array($element)) {
            foreach ($element as $item) {
                $references = $this->extractMedia($index, $item, $statusableElementId , $references);
            }
        }

        return $references;
    }

    /*
     * Check if $attributeValue matches with a media attribute
     *
     * @param mixed $attributeValue
     *
     * @return bool
     */
    protected function isMediaAttribute($attributeValue)
    {
        return is_array($attributeValue) && isset($attributeValue['id']) && isset($attributeValue['format']);
    }
}
