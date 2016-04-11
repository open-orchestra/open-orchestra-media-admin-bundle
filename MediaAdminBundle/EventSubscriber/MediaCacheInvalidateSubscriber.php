<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\DisplayBundle\Manager\CacheableManager;
use OpenOrchestra\BaseBundle\Manager\TagManager;

/**
 * Class MediaCacheInvalidateSubscriber
 */
class MediaCacheInvalidateSubscriber implements EventSubscriberInterface
{
    protected $cacheableManager;
    protected $tagManager;

    /**
     * @param CacheableManager $cacheableManager
     * @param TagManager       $tagManager
     */
    public function __construct(CacheableManager $cacheableManager, TagManager $tagManager)
    {
        $this->cacheableManager = $cacheableManager;
        $this->tagManager = $tagManager;
    }

    /**
     * Invalidate cache on $mediaId
     * 
     * @param string $mediaId
     */
    protected function invalidate($mediaId)
    {
        $this->cacheableManager->invalidateTags(array(
            $this->tagManager->formatMediaIdTag($mediaId)
        ));
    }

    /**
     * Triggered when a media is cropped
     * 
     * @param MediaEvent $event
     */
    public function updateMedia(MediaEvent $event)
    {
        $this->invalidate($event->getMedia()->getId());
    }

    /**
     * Triggered when a media is deleted
     * 
     * @param MediaEvent $event
     */
    public function deleteMedia(MediaEvent $event)
    {
        $this->invalidate($event->getMedia()->getId());
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
