<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\Backoffice\Reference\Strategies\ReferenceStrategyInterface;

/**
 * Class MediaInContentReferenceStrategy
 */
class MediaInContentReferenceStrategy extends AbstractMediaReferenceStrategy implements ReferenceStrategyInterface
{
    /**
     * @param mixed $entity
     *
     * @return boolean
     */
    public function support($entity)
    {
        return ($entity instanceof ContentInterface);
    }

    /**
     * @param mixed $entity
     */
    public function addReferencesToEntity($entity)
    {
        if ($this->support($entity)) {
            $mediaIds = $this->extractMediasFromContent($entity);

            foreach ($mediaIds as $mediaId) {
                /** @var OpenOrchestra\Media\Model\MediaInterface $media */
                $media = $this->mediaRepository->find($mediaId);
                if ($media) {
                    $media->addUseInEntity($entity->getId(), ContentInterface::ENTITY_TYPE);
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
            $contentId = $entity->getId();

            $mediasUsedInContent = $this->mediaRepository
               ->findByUsedInEntity($contentId, ContentInterface::ENTITY_TYPE);

            foreach ($mediasUsedInContent as $media) {
                $media->removeUseInEntity($contentId, ContentInterface::ENTITY_TYPE);
            }
        }
    }

    /**
     * @param ContentInterface $content
     *
     * @return array
     */
    protected function extractMediasFromContent(ContentInterface $content)
    {
        $references = array();

        /** @var ContentAttributeInterface $attribute */
        foreach ($content->getAttributes() as $attribute) {
            $references = array_merge($references, $this->extractMediasFromElement($attribute->getValue()));
        }

        return $references;
    }
}
