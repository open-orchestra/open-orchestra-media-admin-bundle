<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class ImageStrategy
 */
class ImageStrategy implements FileAlternativesStrategyInterface
{
    const MIME_TYPE_FRAGMENT_IMAGE = 'image';

    protected $imageManager;
    protected $tmpDir;
    protected $thumbnailFormat;
    protected $formats;

    /**
     * @param ImageManagerInterface $imageManager
     */
    public function __construct(
        ImageManagerInterface $imageManager,
        $tmpDir,
        $thumbnailFormat,
        array $formats
    ) {
        $this->imageManager = $imageManager;
        $this->tmpDir = $tmpDir;
        $this->thumbnailFormat = $thumbnailFormat;
        $this->formats = $formats;
    }

    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media)
    {
        return strpos($media->getMimeType(), self::MIME_TYPE_FRAGMENT_IMAGE) === 0;
    }

    /**
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $this->imageManager->generateAlternative(
            $media,
            self::THUMBNAIL_PREFIX,
            $this->thumbnailFormat
        );
        $media->setThumbnail(self::THUMBNAIL_PREFIX . '-' . $media->getFilesystemName());

        return $media;
    }

    public function generateAlternatives(MediaInterface $media)
    {
        $filePath = $this->tmpDir . '/' . $media->getFilesystemName();

        foreach ($this->formats as $key => $format) {
            $this->imageManager->resizeAndSaveImage($media, $key, $filePath);
        }

        unlink($filePath);

        return $media;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_alternatives_strategy';
    }
}
