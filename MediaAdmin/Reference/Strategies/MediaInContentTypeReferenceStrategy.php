<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ContentTypeInterface;
use OpenOrchestra\Backoffice\Reference\Strategies\ReferenceStrategyInterface;

/**
 * Class MediaInContentTypeReferenceStrategy
 */
class MediaInContentTypeReferenceStrategy extends AbstractMediaReferenceStrategy implements ReferenceStrategyInterface
{
    /**
     * @param mixed $entity
     *
     * @return boolean
     */
    public function support($entity)
    {
        return $entity instanceof ContentTypeInterface;
    }

    /**
     * @param mixed $entity
     * @param mixed $subEntity
     */
    public function addReferencesToEntity($entity, $subEntity)
    {
        if ($this->support($entity)) {
            $mediaIds = $this->extractMediasFromContentType($entity);

            foreach ($mediaIds as $mediaId) {
                $media = $this->mediaRepository->find($mediaId);
                if ($media) {
                    $media->addUseInEntity($entity->getContentTypeId(), ContentTypeInterface::ENTITY_TYPE);
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
            $contentTypeId = $entity->getContentTypeId();

            $mediasUsedInContentType = $this->mediaRepository
                ->findByUsedInEntity($contentTypeId, ContentTypeInterface::ENTITY_TYPE);

            foreach ($mediasUsedInContentType as $media) {
                $media->removeUseInEntity($contentTypeId, ContentTypeInterface::ENTITY_TYPE);
            }
        }
    }

    /**
     * @param ContentInterface $content
     *
     * @return array
     */
    protected function extractMediasFromContentType(ContentTypeInterface $contentType)
    {
        $mediaIds = array();
        $fields = $contentType->getFields();

        foreach ($fields as $field) {
            if ($this->isMediaAttribute($field->getDefaultValue())) {
                $mediaIds[] = $field->getDefaultValue()['id'];
            }
        }

        return $mediaIds;
    }
}
