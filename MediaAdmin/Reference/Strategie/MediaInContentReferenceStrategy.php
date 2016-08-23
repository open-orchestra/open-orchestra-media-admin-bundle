<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategie;

use OpenOrchestra\ModelInterface\Model\StatusableInterface;
use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\Backoffice\Reference\Strategie\ReferenceStrategyInterface;

/**
 * Class MediaInContentReferenceStrategy
 */
class MediaInContentReferenceStrategy extends AbstractMediaReferenceStrategy implements ReferenceStrategyInterface
{
    /**
     * @param StatusableInterface $statusableElement
     *
     * @return boolean
     */
    public function support(StatusableInterface $statusableElement)
    {
        return ($statusableElement instanceof ContentInterface);
    }

    /**
     * @param StatusableInterface $statusableElement
     */
    public function addReferencesToEntity(StatusableInterface $statusableElement)
    {
        $mediaIds = $this->extractMediasFromContent($statusableElement);

        foreach ($mediaIds as $mediaId) {
            /** @var MediaInterface $media */
            $media = $this->mediaRepository->find($mediaId);
            $media->addUseInContent($statusableElement->getId());
        }
    }

    /**
     * @param StatusableInterface $statusableElement
     */
    public function removeReferencesToEntity(StatusableInterface $statusableElement)
    {
        $contentId = $statusableElement->getId();

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
