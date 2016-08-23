<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategie;

use OpenOrchestra\ModelInterface\Model\StatusableInterface;
use OpenOrchestra\ModelInterface\Model\ReadNodeInterface;
use OpenOrchestra\Backoffice\Reference\Strategie\ReferenceStrategyInterface;

/**
 * Class MediaInNodeReferenceStrategy
 */
class MediaInNodeReferenceStrategy extends AbstractMediaReferenceStrategy implements ReferenceStrategyInterface
{
    /**
     * @param StatusableInterface $statusableElement
     *
     * @return boolean
     */
    public function support(StatusableInterface $statusableElement)
    {
        return ($statusableElement instanceof ReadNodeInterface);
    }

    /**
     * @param StatusableInterface $statusableElement
     */
    public function addReferencesToEntity(StatusableInterface $statusableElement)
    {
        $mediaIds = $this->extractMediasFromNode($statusableElement);

        foreach ($mediaIds as $mediaId) {
            /** @var MediaInterface $media */
            $media = $this->mediaRepository->find($mediaId);
            $media->addUseInNode($statusableElement->getId());
        }
    }

    /**
     * @param StatusableInterface $statusableElement
     */
    public function removeReferencesToEntity(StatusableInterface $statusableElement)
    {
        $nodeId = $statusableElement->getId();

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
