<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Video;

/**
 * Interface VideoManagerInterface
 */
interface VideoManagerInterface
{
    /**
     * Extract an image at $timeFrame from $filePath
     * 
     * @param string $filePath
     * @param int    $timeFrame
     * 
     * @return string
     */
    public function extractImageFromVideo($filePath, $timeFrame);
}
