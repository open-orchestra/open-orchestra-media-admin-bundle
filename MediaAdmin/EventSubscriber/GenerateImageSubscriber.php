<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickImageManager;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\MediaInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class GenerateImageSubscriber
 */
class GenerateImageSubscriber implements EventSubscriberInterface
{
    public $medias = array();

    protected $imagickImageManager;

    /**
     * @param ImagickImageManager $imagickImageManager
     */
    public function __construct(ImagickImageManager $imagickImageManager)
    {
        $this->imagickImageManager = $imagickImageManager;
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
    public function generateImages()
    {
        /** @var MediaInterface $media */
        foreach ($this->medias as $media) {
            $this->imagickImageManager->generateAllThumbnails($media);
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            MediaEvents::ADD_IMAGE => 'addMedia',
            KernelEvents::TERMINATE => 'generateImages',
        );
    }
}
