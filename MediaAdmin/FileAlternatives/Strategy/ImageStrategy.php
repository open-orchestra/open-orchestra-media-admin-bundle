<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;

/**
 * Class ImageStrategy
 */
class ImageStrategy implements FileAlternativesStrategyInterface
{
    const MIME_TYPE_FRAGMENT_IMAGE = 'image';

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
    public function generateThumbnail(MediaInterface $media)
    {
        $media->setThumbnail($media->getFilesystemName()); // <= good practice to generate thumbnail name ????
        // TODO: generate file

        return $media;
    }

    public function generateAlternatives(MediaInterface $media)
    {
        // TODO: generate alternatives

        return $media;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_alternatives_strategy';
    }
}
