<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies;

use OpenOrchestra\ModelInterface\Model\ContentAttributeInterface;
use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;

/**
 * Class ExtractReferenceFromContentStrategy
 */
class ExtractReferenceFromContentStrategy extends AbstractExtractReferenceStrategy
{
    const REFERENCE_PREFIX = 'content-';

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return bool
     */
    public function support(StatusableInterface $statusableElement)
    {
        return $statusableElement instanceof ContentInterface;
    }

    /**
     * @param StatusableInterface|ContentInterface $statusableElement
     *
     * @return array
     */
    public function extractReference(StatusableInterface $statusableElement)
    {
        $references = array();

        /** @var ContentAttributeInterface $attribute */
        foreach ($statusableElement->getAttributes() as $key => $attribute) {
            $references = $this->extractMedia($key, $attribute->getValue(), $statusableElement->getId(), $references);
        }

        return $references;
    }

    /**
     * Get Reference pattern for $statusableElementId
     *
     * @param string $statusableElementId
     *
     * return string
     */
    public function getReferencePattern($statusableElementId)
    {
        return self::REFERENCE_PREFIX . $statusableElementId;
    }

    /**
     * Format a reference
     *
     * @param string $index
     * @param string $statusableElementId
     *
     * @return string
     */
    protected function formatReference($index, $statusableElementId)
    {
        return $this->getReferencePattern($statusableElementId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'content';
    }
}
