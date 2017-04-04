<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use Symfony\Component\Filesystem\Filesystem;
use OpenOrchestra\Media\Manager\MediaStorageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class AudioStrategy
 */
class AudioStrategy extends AbstractFileAlternativesStrategy
{
    const MEDIA_TYPE = 'audio';
    const MIME_TYPE_FRAGMENT_AUDIO = 'audio';

    protected $thumbnail;

    /**
     * @param Filesystem                   $fileSystem
     * @param MediaStorageManagerInterface $mediaStorageManager
     * @param string                       $tmpDir
     * @param string                       $thumbnail
     */
    public function __construct(
        Filesystem $fileSystem,
        MediaStorageManagerInterface $mediaStorageManager,
        $tmpDir,
        $thumbnail
    ) {
        parent::__construct($fileSystem, $mediaStorageManager, $tmpDir);
        $this->thumbnail = $thumbnail;
    }

    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media)
    {
        return strpos($media->getMimeType(), self::MIME_TYPE_FRAGMENT_AUDIO) === 0;
    }

    /**
     * @param MediaInterface $media
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $media->setThumbnail($this->thumbnail);
    }

    /**
     * Delete the thumbnail of $media
     * That strategy does nothing as the thumbnail is the same for all default type medias
     *
     * @param MediaInterface $media
     */
    public function deleteThumbnail(MediaInterface $media)
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'audio_alternatives_strategy';
    }
}
