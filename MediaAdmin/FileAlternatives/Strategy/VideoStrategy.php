<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use Symfony\Component\Filesystem\Filesystem;
use OpenOrchestra\MediaFileBundle\Manager\MediaStorageManager;
use OpenOrchestra\MediaAdmin\FileUtils\Video\VideoManagerInterface;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class VideoStrategy
 */
class VideoStrategy extends AbstractFileAlternativesStrategy
{
    const MEDIA_TYPE = 'video';
    const MIME_TYPE_FRAGMENT_VIDEO = 'video';

    protected $videoManager;
    protected $imageManager;
    protected $thumbnailFormat;

    /**
     * @param Filesystem            $fileSystem
     * @param MediaStorageManager   $mediaStorageManager
     * @param VideoManagerInterface $videoManager
     * @param ImageManagerInterface $imageManager
     * @param string                $tmpDir
     * @param array                 $thumbnailFormat
     */
    public function __construct(
        Filesystem $fileSystem,
        MediaStorageManager $mediaStorageManager,
        VideoManagerInterface $videoManager,
        ImageManagerInterface $imageManager,
        $tmpDir,
        array $thumbnailFormat
    ) {
        $this->fileSystem = $fileSystem;
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
            $this->tmpDir . DIRECTORY_SEPARATOR . $fileName,
            1
        );

        $thumbnailPath = $this->imageManager->generateAlternative($extractedImagePath, $this->thumbnailFormat);

        if ('' !== $thumbnailPath) {
            $thumbnailName = self::THUMBNAIL_PREFIX . '-' . pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
            $this->mediaStorageManager->uploadFile($thumbnailName, $thumbnailPath);
        }

        if (trim($extractedImagePath, DIRECTORY_SEPARATOR) !== trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
            $this->fileSystem->remove(array($extractedImagePath));
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
