<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Interface ImageManagerInterface
 */
interface ImageManagerInterface
{
    /**
     * @param MediaInterface $media
     * @param int            $x
     * @param int            $y
     * @param int            $h
     * @param int            $w
     * @param string         $format
     */
    public function crop(MediaInterface $media, $x, $y, $h, $w, $format);

    /**
     * @param MediaInterface $media
     * @param string         $format
     */
    public function override(MediaInterface $media, $format);

    /**
     * @param MediaInterface $media
     * @param string         $format
     * @param string         $filePath
     */
    public function resizeAndSaveImage(MediaInterface $media, $format, $filePath);
}
