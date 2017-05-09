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
     * Get the $media type supported by the strategy
     *
     * @return string
     */
    public function getMediaType();

    /**
     * Set specific media information (extension, size, ...) in media
     *
     * @param MediaInterface $media;
     * @param array          $languages;
     */
    public function setMediaInformation(MediaInterface $media, array $languages);

    /**
     * Generate a thumbnail for $media
     *
     * @param MediaInterface $media
     */
    public function generateThumbnail(MediaInterface $media);

    /**
     * Generate all alternatives for $media
     *
     * @param MediaInterface $media
     */
    public function generateAlternatives(MediaInterface $media);

    /**
     * Delete the thumbnail of $media
     *
     * @param MediaInterface $media
     */
    public function deleteThumbnail(MediaInterface $media);

    /**
     * Delete all alternatives of $media
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
