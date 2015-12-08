<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaFileBundle\Manager\MediaStorageManager;
use OpenOrchestra\MediaAdmin\FileUtils\Video\VideoManagerInterface;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class VideoStrategy
 */
class VideoStrategy extends AbstractFileAlternativesStrategy
{
    const MIME_TYPE_FRAGMENT_VIDEO = 'video';

    protected $videoManager;
    protected $imageManager;
    protected $thumbnailFormat;

    /**
     * @param MediaStorageManager   $mediaStorageManager
     * @param VideoManagerInterface $videoManager
     * @param ImageManagerInterface $imageManager
     * @param string                $tmpDir
     * @param array                 $thumbnailFormat
     */
    public function __construct(
        MediaStorageManager $mediaStorageManager,
        VideoManagerInterface $videoManager,
        ImageManagerInterface $imageManager,
        $tmpDir,
        array $thumbnailFormat
    ) {
        $this->mediaStorageManager = $mediaStorageManager;
        $this->videoManager = $videoManager;
        $this->imageManager = $imageManager;
        $this->tmpDir = $tmpDir;
        $this->thumbnailFormat = $thumbnailFormat;
    }

    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media)
    {
        return strpos($media->getMimeType(), self::MIME_TYPE_FRAGMENT_VIDEO) === 0;
    }

    /**
     * Generate a thumbnail for $media
     *
     * @param MediaInterface $media
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $fileName = $media->getFilesystemName();
        $thumbnailName = '';

        $extractedImagePath = $this->videoManager->extractImageFromVideo(
            $this->tmpDir . DIRECTORY_SEPARATOR . $fileName
        );

        $thumbnailPath = $this->imageManager->generateAlternative($extractedImagePath, $this->thumbnailFormat);

        if ('' !== $thumbnailPath) {
            $thumbnailName = self::THUMBNAIL_PREFIX . '-' . pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
            $this->mediaStorageManager->uploadFile($thumbnailName, $thumbnailPath);
        }

        if (trim($extractedImagePath, DIRECTORY_SEPARATOR) !== trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
            unlink($extractedImagePath);
        }

        $media->setThumbnail($thumbnailName);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'video_alternatives_strategy';
    }
}
