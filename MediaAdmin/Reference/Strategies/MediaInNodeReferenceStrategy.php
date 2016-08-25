<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ReadNodeInterface;
use OpenOrchestra\Backoffice\Reference\Strategies\ReferenceStrategyInterface;

/**
 * Class MediaInNodeReferenceStrategy
 */
class MediaInNodeReferenceStrategy extends AbstractMediaReferenceStrategy implements ReferenceStrategyInterface
{
    /**
     * @param mixed $entity
     *
     * @return boolean
     */
    public function support($entity)
    {
        return ($entity instanceof ReadNodeInterface);
    }

    /**
     * @param mixed $entity
     */
    public function addReferencesToEntity($entity)
    {
        $mediaIds = $this->extractMediasFromNode($entity);

        foreach ($mediaIds as $mediaId) {
            /** @var MediaInterface $media */
            $media = $this->mediaRepository->find($mediaId);
            $media->addUseInNode($entity->getId());
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeReferencesToEntity($entity)
    {
        $nodeId = $entity->getId();

        $mediasUsedInNode = $this->mediaRepository->findUsedInNode($nodeId);

        foreach ($mediasUsedInNode as $media) {
            $media->removeUseInNode($nodeId);
        }
    }

    /**
     * @param ReadNodeInterface $node
     *
     * @return array
     */
    protected function extractMediasFromNode(ReadNodeInterface $node)
    {
        $references = array();

        /** @var BlockInterface $block */
        foreach ($node->getBlocks() as $block) {
            $references = $this->extractMediasFromElement($block->getAttributes(), $references);
        }

        return $references;
    }
}
