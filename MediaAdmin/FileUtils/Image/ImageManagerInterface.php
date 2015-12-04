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
     * Crop $filePath with ($x, $y, $h, $w) and resize it to the $formatName
     * 
     * @param $filePath
     * @param $x
     * @param $y
     * @param $h
     * @param $w
     * @param $formatName
     */
    public function cropAndResize($filePath, $x, $y, $h, $w, $formatName);
}
