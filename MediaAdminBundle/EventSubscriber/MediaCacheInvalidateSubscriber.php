<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\Media\Model\MediaInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\DisplayBundle\Manager\CacheableManager;
use OpenOrchestra\BaseBundle\Manager\TagManager;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\ModelInterface\Model\ContentTypeInterface;

/**
 * Class MediaCacheInvalidateSubscriber
 */
class MediaCacheInvalidateSubscriber implements EventSubscriberInterface
{
    protected $cacheableManager;
    protected $tagManager;

    /**
     * @param CacheableManager        $cacheableManager
     * @param TagManager              $tagManager
     */
    public function __construct(
        CacheableManager $cacheableManager,
        TagManager $tagManager
    ) {
        $this->cacheableManager = $cacheableManager;
        $this->tagManager = $tagManager;
    }

    /**
     * Invalidate cache on $mediaId
     * 
     * @param MediaInterface $media
     */
    protected function invalidate($media)
    {
        $tags = array($this->tagManager->formatMediaIdTag($media->getId()));

        $nodeUsage = $media->getUseReferences(NodeInterface::ENTITY_TYPE);

        foreach ($nodeUsage as $nodeId) {
            $tags[] = $this->tagManager->formatNodeIdTag($nodeId);
        }

        $contentUsage = $media->getUseReferences(ContentInterface::ENTITY_TYPE);
        foreach ($contentUsage as $contentId) {
            $tags[] = $this->tagManager->formatContentIdTag($contentId);
        }

        $contentTypeUsage = $media->getUseReferences(ContentTypeInterface::ENTITY_TYPE);
        foreach ($contentTypeUsage as $contentTypeId) {
            $tags[] = $this->tagManager->formatContentTypeTag($contentTypeId);
        }

        $this->cacheableManager->invalidateTags($tags);
    }

    /**
     * Triggered when a media is cropped
     * 
     * @param MediaEvent $event
     */
    public function updateMedia(MediaEvent $event)
    {
        $this->invalidate($event->getMedia());
    }

    /**
     * Triggered when a media is deleted
     * 
     * @param MediaEvent $event
     */
    public function deleteMedia(MediaEvent $event)
    {
        $this->invalidate($event->getMedia());
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            MediaEvents::MEDIA_UPDATE => 'updateMedia',
            MediaEvents::MEDIA_DELETE => 'deleteMedia'
       );
    }
}
