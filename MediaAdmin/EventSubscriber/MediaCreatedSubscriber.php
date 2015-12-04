<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;

/**
 * Class MediaCreatedSubscriber
 */
class MediaCreatedSubscriber implements EventSubscriberInterface
{
    public $medias = array();
    protected $fileAlternativesManager;
    protected $objectManager;

    /**
     * @param fileAlternativesManager $fileAlternativesManager
     * @param ObjectManager           $objectManager
     */
    public function __construct(FileAlternativesManager $fileAlternativesManager, ObjectManager $objectManager)
    {
        $this->fileAlternativesManager = $fileAlternativesManager;
        $this->objectManager = $objectManager;
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
        }

        $this->objectManager->flush();
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            MediaEvents::MEDIA_ADD => 'addMedia',
            KernelEvents::TERMINATE => 'generateAlternatives'
        );
    }
}
