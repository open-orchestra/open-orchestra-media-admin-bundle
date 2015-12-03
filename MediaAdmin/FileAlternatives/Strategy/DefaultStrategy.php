<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class DefaultStrategy
 */
class DefaultStrategy extends AbstractFileAlternativesStrategy
{
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
        return true;
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
        return 'default_alternatives_strategy';
    }
}
