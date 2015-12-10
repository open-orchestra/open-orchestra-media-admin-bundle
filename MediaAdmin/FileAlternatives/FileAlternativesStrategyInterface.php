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
     */
    public function generateThumbnail(MediaInterface $media);

    /**
     * Generate all aternatives for $media
     *
     * @param MediaInterface $media
     */
    public function generateAlternatives(MediaInterface $media);

    /**
     * Get alternatives from $media
     * 
     * @param $media
     * 
     * @return array
     */
    public function getAlternatives(MediaInterface $media);

    /**
     * Delete the thumbnail of $media
     *
     * @param MediaInterface $media
     */
    public function deleteThumbnail(MediaInterface $media);

    /**
     * Delete all aternatives of $media
     *
     * @param MediaInterface $media
     */
    public function deleteAlternatives(MediaInterface $media);

    /**
     * Override the file alternative for $media with $newFile and run it
     * 
     * @param MediaInterface $media
     * @param string         $newFilePath
     * @param string         $formatName
     */
    public function overrideAlternative(MediaInterface $media, $newFilePath, $formatName);

    /**
     * Return the name of the strategy
     * 
     * @return string
     */
    public function getName();
}
