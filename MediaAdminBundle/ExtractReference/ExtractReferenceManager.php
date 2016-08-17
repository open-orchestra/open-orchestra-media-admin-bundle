<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference;

use OpenOrchestra\MediaAdminBundle\Exceptions\ExtractReferenceStrategyNotFound;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;

/**
 * Class ExtractReferenceManager
 */
class ExtractReferenceManager
{
    protected $strategies = array();

    /**
     * @param ExtractReferenceInterface $strategy
     */
    public function addStrategy(ExtractReferenceInterface $strategy)
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return array
     *
     * @throws ExtractReferenceStrategyNotFound
     */
    public function extractReference(StatusableInterface $statusableElement)
    {
        /** @var ExtractReferenceInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($statusableElement)) {
                return $strategy->extractReference($statusableElement);
            }
        }

        throw new ExtractReferenceStrategyNotFound();
    }

    /**
     * Get Reference pattern for $statusableElement
     *
     * @param StatusableInterface $statusableElement
     *
     * return string
     *
     * @throws ExtractReferenceStrategyNotFound
     */
    public function getReferencePattern(StatusableInterface $statusableElement)
    {
        /** @var ExtractReferenceInterface $strategy */
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($statusableElement)) {
                return $strategy->getReferencePattern($statusableElement->getId());
            }
        }

        throw new ExtractReferenceStrategyNotFound();
    }

    /**
     * Get cache tag of references
     *
     * @param array $references
     *
     * @return array
     */
    public function getStatusableElementCacheTag(array $references)
    {
        $invalidateTag = array();
        /** @var ExtractReferenceInterface $strategy */
        foreach ($references as $reference) {
            foreach ($this->strategies as $strategy) {
                if ($strategy->supportReference($reference)) {
                    $tag = $strategy->getStatusableElementCacheTag($reference);
                    if (null !== $tag) {
                        $invalidateTag[] = $tag;
                    }
                }
            }
        }

        return $invalidateTag;
    }
}
