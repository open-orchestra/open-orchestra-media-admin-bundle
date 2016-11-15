<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ReadNodeInterface;
use OpenOrchestra\Backoffice\Reference\Strategies\ReferenceStrategyInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Event\NodeEvent;

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
     * @param mixed $event
     */
    public function addReferencesToEntity($event)
    {
        $node = $event->getNode();
        if ($this->support($node)) {
            $mediaIds = $this->extractMediasFromNode($event);

            foreach ($mediaIds as $mediaId) {
                /** @var OpenOrchestra\Media\Model\MediaInterface $media */
                $media = $this->mediaRepository->find($mediaId);
                if ($media) {
                    $media->addUseInEntity($node->getId(), NodeInterface::ENTITY_TYPE);
                }
            }
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeReferencesToEntity($entity)
    {
        if ($this->support($entity)) {
            $nodeId = $entity->getId();

            $mediasUsedInNode = $this->mediaRepository->findByUsedInEntity($nodeId, NodeInterface::ENTITY_TYPE);

            foreach ($mediasUsedInNode as $media) {
                $media->removeUseInEntity($nodeId, NodeInterface::ENTITY_TYPE);
            }
        }
    }

    /**
     * @param NodeEvent $event
     *
     * @return array
     */
    protected function extractMediasFromNode(NodeEvent $event)
    {
        $references = array();

        $blocks = ($event->getBlock() != null) ? array($event->getBlock()) : $event->getNode()->getBlocks();

        /** @var BlockInterface $block */
        foreach ($blocks as $block) {
            $references = array_merge($references, $this->extractMediasFromElement($block->getAttributes()));
        }

        return $references;
    }
}
