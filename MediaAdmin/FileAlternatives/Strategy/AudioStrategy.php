<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class AudioStrategy
 */
class AudioStrategy extends AbstractFileAlternativesStrategy
{
    const MIME_TYPE_FRAGMENT_AUDIO = 'audio';

    protected $thumbnail;

    /**
     * @param string $tmpDir
     * @param string $thumbnail
     */
    public function __construct($tmpDir, $thumbnail)
    {
        $this->tmpDir = $tmpDir;
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
     *
     * @return MediaInterface
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $media->setThumbnail($this->thumbnail);

        return $media;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'audio_alternatives_strategy';
    }
}
