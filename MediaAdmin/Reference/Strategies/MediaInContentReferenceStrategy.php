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
        $mediaIds = $this->extractMediasFromContent($entity);

        foreach ($mediaIds as $mediaId) {
            /** @var MediaInterface $media */
            $media = $this->mediaRepository->find($mediaId);
            $media->addUseInContent($entity->getId());
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeReferencesToEntity($entity)
    {
        $contentId = $entity->getId();

        $mediasUsedInContent = $this->mediaRepository->findUsedInContent($contentId);

        foreach ($mediasUsedInContent as $media) {
            $media->removeUseInContent($contentId);
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
            $references = $this->extractMediasFromElement($attribute->getValue(), $references);
        }

        return $references;
    }
}
