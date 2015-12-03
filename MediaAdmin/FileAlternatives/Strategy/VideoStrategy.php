<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager;
use OpenOrchestra\MediaAdmin\FileUtils\Video\VideoManagerInterface;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class VideoStrategy
 */
class VideoStrategy extends AbstractFileAlternativesStrategy
{
    const MIME_TYPE_FRAGMENT_VIDEO = 'video';

    protected $uploadedMediaManager;
    protected $videoManager;
    protected $imageManager;
    protected $thumbnailFormat;

    /**
     * @param UploadedMediaManager  $uploadedMediaManager
     * @param VideoManagerInterface $videoManager
     * @param ImageManager          $imageManager
     * @param string                $tmpDir
     * @param array                 $thumbnailFormat
     */
    public function __construct(
        UploadedMediaManager $uploadedMediaManager,
        VideoManagerInterface $videoManager,
        ImageManagerInterface $imageManager,
        $tmpDir,
        array $thumbnailFormat
    ) {
        $this->uploadedMediaManager = $uploadedMediaManager;
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
     *
     * @return MediaInterface
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $fileName = $media->getFilesystemName();
        $thumbnailName = '';

        $extractedImagePath = $this->videoManager->extractImageFromVideo(
            $this->tmpDir . DIRECTORY_SEPARATOR . $fileName
        );

        $thumbnailPath = $this->imageManager->generateAlternative($extractedImagePath, $this->thumbnailFormat);

        if ($thumbnailPath != '') {
            $thumbnailName = self::THUMBNAIL_PREFIX . '-' . pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
            $this->uploadedMediaManager->uploadContent($thumbnailName, file_get_contents($thumbnailPath));

            if (trim($thumbnailPath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
                unlink($thumbnailPath);
            }
        }

        if (trim($extractedImagePath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
            unlink($extractedImagePath);
        }

        $media->setThumbnail($thumbnailName);

        return $media;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'video_alternatives_strategy';
    }
}
