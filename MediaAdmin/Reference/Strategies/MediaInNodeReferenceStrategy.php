<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ReadNodeInterface;
use OpenOrchestra\Backoffice\Reference\Strategies\ReferenceStrategyInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;

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
            /** @var OpenOrchestra\Media\Model\MediaInterface $media */
            $media = $this->mediaRepository->find($mediaId);
            if ($media) {
                $media->addUseInEntity($entity->getId(), NodeInterface::ENTITY_TYPE);
            }
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeReferencesToEntity($entity)
    {
        $nodeId = $entity->getId();

        $mediasUsedInNode = $this->mediaRepository->findByUsedInEntity($nodeId, NodeInterface::ENTITY_TYPE);

        foreach ($mediasUsedInNode as $media) {
            $media->removeUseInEntity($nodeId, NodeInterface::ENTITY_TYPE);
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
