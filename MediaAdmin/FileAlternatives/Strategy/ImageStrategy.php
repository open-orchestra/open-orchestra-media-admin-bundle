<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;
use OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class ImageStrategy
 */
class ImageStrategy implements FileAlternativesStrategyInterface
{
    const MIME_TYPE_FRAGMENT_IMAGE = 'image';

    protected $uploadedMediaManager;
    protected $imageManager;
    protected $tmpDir;
    protected $thumbnailFormat;
    protected $alternativeFormats;

    /**
     * @param UploadedMediaManager  $uploadedMediaManager
     * @param ImageManagerInterface $imageManager
     * @param string                $tmpDir
     * @param array                 $thumbnailFormat
     * @param array                 $alternativeFormats
     */
    public function __construct(
        UploadedMediaManager $uploadedMediaManager,
        ImageManagerInterface $imageManager,
        $tmpDir,
        array $thumbnailFormat,
        array $alternativeFormats
    ) {
        $this->uploadedMediaManager = $uploadedMediaManager;
        $this->imageManager = $imageManager;
        $this->tmpDir = $tmpDir;
        $this->thumbnailFormat = $thumbnailFormat;
        $this->alternativeFormats = $alternativeFormats;
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
     * Generate a thumbnail for $media
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $thumbnailName = $this->generateAlternative(
            $media->getFilesystemName(),
            self::THUMBNAIL_PREFIX,
            $this->thumbnailFormat
        );

        $media->setThumbnail($thumbnailName);

        return $media;
    }

    /**
     * Generate all aternatives for $media
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateAlternatives(MediaInterface $media)
    {
        $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName();

        foreach ($this->alternativeFormats as $key => $format) {
            $this->generateAlternative($media->getFilesystemName(), $key, $format);
        }

        if (trim($filePath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
            unlink($filePath);
        }

        return $media;
    }

    /**
     * Generate a $fileName alternative in $formatName with $formatSize
     *
     * @param string $fileName
     * @param string $formatName
     * @param array  $formatSize
     *
     * @return string
     */
    protected function generateAlternative($fileName, $formatName, array $formatSize)
    {
        $alternativePath = $this->imageManager->generateAlternative(
            $this->tmpDir . DIRECTORY_SEPARATOR . $fileName,
            $formatSize
        );

        if ($alternativePath != '') {
            $alternativeName = $formatName . '-' . $fileName;
            $this->uploadedMediaManager->uploadContent(
                $alternativeName,
                file_get_contents($alternativePath)
            );
            if (trim($alternativePath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
                unlink($alternativePath);
            }
        }

        return $alternativeName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_alternatives_strategy';
    }
}
