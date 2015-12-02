<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Video;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

/**
 * Class FFmpegVideoManager
 */
class FFmpegVideoManager implements VideoManagerInterface
{
    protected $ffmpeg;

    /**
     * @param FFMpeg $FFmpeg
     */
    public function __construct(FFMpeg $FFmpeg)
    {
        $this->ffmpeg = $FFmpeg;
    }

    /**
     * @param string $filePath
     * @param int    $timeFrame
     * 
     * @return string
     */
    public function extractImageFromVideo($filePath, $timeFrame = 1)
    {
        $pathInfo = pathinfo($filePath);
        $extractedImagePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR
            . time() . $pathInfo['basename'] . '-' . $timeFrame . '.jpg';

        $video = $this->ffmpeg->open($filePath);
        $video->frame(TimeCode::fromSeconds($timeFrame))->save($extractedImagePath);

        return $extractedImagePath;
    }
}
