<?php

namespace OpenOrchestra\MediaAdmin\Thumbnail\Strategies;

use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdmin\Thumbnail\ThumbnailInterface;

/**
 * Class ImageToThumbnailManager
 */
class ImageToThumbnailManager implements ThumbnailInterface
{
    const MIME_TYPE_FRAGMENT_IMAGE = 'image';

    protected $tmpDir;
    protected $dispatcher;

    /**
     * @param string                            $tmpDir
     * @param TraceableEventDispatcherInterface $dispatcher
     */
    public function __construct($tmpDir, TraceableEventDispatcherInterface $dispatcher)
    {
        $this->tmpDir = $tmpDir;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media)
    {
        return strpos($media->getMimeType(), self::MIME_TYPE_FRAGMENT_IMAGE) === 0;
    }

    /**
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateThumbnailName(MediaInterface $media)
    {
        $media->setThumbnail($media->getFilesystemName());
        $event = new MediaEvent($media);
        $this->dispatcher->dispatch(MediaEvents::ADD_IMAGE, $event);

        return $media;
    }

    /**
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateThumbnail(MediaInterface $media)
    {
        return $media;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_to_thumbnail';
    }
}
