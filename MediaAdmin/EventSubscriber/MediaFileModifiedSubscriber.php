<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;

/**
 * Class MediaFileModifiedSubscriber
 */
class MediaFileModifiedSubscriber implements EventSubscriberInterface
{
    public $medias = array();
    protected $fileAlternativesManager;

    /**
     * @param fileAlternativesManager  $fileAlternativesManager
     */
    public function __construct(FileAlternativesManager $fileAlternativesManager)
    {
        $this->fileAlternativesManager = $fileAlternativesManager;
    }

    /**
     * @param MediaEvent $event
     */
    public function addMedia(MediaEvent $event)
    {
        $media = $event->getMedia();
        $this->medias[] = $media;
    }

    /**
     * Generate images
     */
    public function generateAlternatives()
    {
        /** @var MediaInterface $media */
        foreach ($this->medias as $media) {
            $media = $this->fileAlternativesManager->generateThumbnail($media);
            $media = $this->fileAlternativesManager->generateAlternatives($media);
            // Todo: Flush updated media with thumbnailname
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            MediaEvents::ADD_MEDIA => 'addMedia',
            KernelEvents::TERMINATE => 'generateAlternatives'
        );
    }
}
