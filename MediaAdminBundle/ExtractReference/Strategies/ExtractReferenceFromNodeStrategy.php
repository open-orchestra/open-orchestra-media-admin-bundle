<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies;

use OpenOrchestra\ModelInterface\Model\BlockInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;

/**
 * Class ExtractReferenceFromNodeStrategy
 */
class ExtractReferenceFromNodeStrategy extends AbstractExtractReferenceStrategy
{
    const REFERENCE_PREFIX = 'node-';

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return bool
     */
    public function support(StatusableInterface $statusableElement)
    {
        return $statusableElement instanceof NodeInterface;
    }

    /**
     * @param StatusableInterface|NodeInterface $statusableElement
     *
     * @return array
     */
    public function extractReference(StatusableInterface $statusableElement)
    {
        $references = array();

        /** @var BlockInterface $block */
        foreach ($statusableElement->getBlocks() as $key => $block) {
            $references = $this->extractMedia($key, $block->getAttributes(), $statusableElement->getId(), $references);
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
        return self::REFERENCE_PREFIX . $statusableElementId . '-';
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
        return $this->getReferencePattern($statusableElementId) . $index;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'node';
    }
}
