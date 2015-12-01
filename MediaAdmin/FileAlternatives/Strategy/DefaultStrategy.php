<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;

/**
 * Class DefaultStrategy
 */
class DefaultStrategy implements FileAlternativesStrategyInterface
{
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
        // TODO: attribute a default file for thumbnail

        return $media;
    }

    public function generateAlternatives(MediaInterface $media)
    {
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
