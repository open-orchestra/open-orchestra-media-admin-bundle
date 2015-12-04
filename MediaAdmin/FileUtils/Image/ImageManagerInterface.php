<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Interface ImageManagerInterface
 */
interface ImageManagerInterface
{
    /**
     * Generate a variant of image located at $filePath using $format
     * 
     * @param string $filePath
     * @param array  $format
     * 
     * @return string
     */
    public function generateAlternative($filePath, array $format);

    /**
     * Extract an image from the $page of $filePath
     * 
     * @param string $filePath
     * @param int    $page
     * 
     * @return string
     */
    public function extractImageFromPdf($filePath, $page = 0);

    /**
     * @param MediaInterface $media
     * @param int            $x
     * @param int            $y
     * @param int            $h
     * @param int            $w
     * @param string         $format
     */
    public function crop(MediaInterface $media, $x, $y, $h, $w, $format);
}
