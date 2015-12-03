<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Interface FileAlternativesStrategyInterface
 */
interface FileAlternativesStrategyInterface
{
     const THUMBNAIL_PREFIX = 'thumbnail';

    /**
     * Return true if the strategy supports $media
     * 
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media);

    /**
     * Generate a thumbnail for $media
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateThumbnail(MediaInterface $media);

    /**
     * Generate all aternatives for $media
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateAlternatives(MediaInterface $media);

    /**
     * return the name of the strategy
     * 
     * @return string
     */
    public function getName();
}
