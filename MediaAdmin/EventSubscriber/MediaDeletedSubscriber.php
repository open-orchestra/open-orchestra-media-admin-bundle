<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class MediaDeletedSubscriber
 */
class MediaDeletedSubscriber implements EventSubscriberInterface
{
    protected $medias = array();
    protected $fileAlternativesManager;

    /**
     * @param FileAlternativesManager $fileAlternativesManager
     */
    public function __construct(FileAlternativesManager $fileAlternativesManager)
    {
        $this->fileAlternativesManager = $fileAlternativesManager;
    }

    /**
     * @param MediaEvent $event
     */
    public function deleteMedia(MediaEvent $event)
    {
        $media = $event->getMedia();
        $this->medias[] = $media;
    }

    /**
     * Remove medias
     */
    public function deleteAlternatives()
    {
        /** @var MediaInterface $media */
        foreach ($this->medias as $media) {
            $this->fileAlternativesManager->deleteThumbnail($media);
            $this->fileAlternativesManager->deleteAlternatives($media);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            MediaEvents::MEDIA_DELETE => 'deleteMedia',
            KernelEvents::TERMINATE => 'deleteAlternatives',
        );
    }
}
