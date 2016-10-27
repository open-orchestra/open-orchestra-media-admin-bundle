<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ReadBlockInterface;
use OpenOrchestra\Backoffice\Reference\Strategies\ReferenceStrategyInterface;
use OpenOrchestra\ModelInterface\Model\BlockInterface;

/**
 * Class MediaInBlockReferenceStrategy
 */
class MediaInBlockReferenceStrategy extends AbstractMediaReferenceStrategy implements ReferenceStrategyInterface
{
    /**
     * @param mixed $entity
     *
     * @return boolean
     */
    public function support($entity)
    {
        return ($entity instanceof ReadBlockInterface);
    }

    /**
     * @param mixed $entity
     */
    public function addReferencesToEntity($entity)
    {
        if ($this->support($entity)) {
            $mediaIds = $this->extractMediasFromElement($entity->getAttributes());

            foreach ($mediaIds as $mediaId) {
                /** @var OpenOrchestra\Media\Model\MediaInterface $media */
                $media = $this->mediaRepository->find($mediaId);
                if ($media) {
                    $media->addUseInEntity($entity->getId(), BlockInterface::ENTITY_TYPE);
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
            $blockId = $entity->getId();

            $mediasUsedInNode = $this->mediaRepository->findByUsedInEntity($blockId, BlockInterface::ENTITY_TYPE);

            foreach ($mediasUsedInNode as $media) {
                $media->removeUseInEntity($blockId, BlockInterface::ENTITY_TYPE);
            }
        }
    }
}
