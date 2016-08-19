<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceManager;
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
    protected $extractReferenceManager;

    /**
     * @param CacheableManager        $cacheableManager
     * @param TagManager              $tagManager
     * @param ExtractReferenceManager $extractReferenceManager
     */
    public function __construct(
        CacheableManager $cacheableManager,
        TagManager $tagManager,
        ExtractReferenceManager $extractReferenceManager
    ) {
        $this->cacheableManager = $cacheableManager;
        $this->tagManager = $tagManager;
        $this->extractReferenceManager = $extractReferenceManager;
    }

    /**
     * Invalidate cache on $mediaId
     * 
     * @param MediaInterface $media
     */
    protected function invalidate($media)
    {
        $tags = $this->extractReferenceManager->getStatusableElementCacheTag($media->getUsageReference());
        $tags[] = $this->tagManager->formatMediaIdTag($media->getId());
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
