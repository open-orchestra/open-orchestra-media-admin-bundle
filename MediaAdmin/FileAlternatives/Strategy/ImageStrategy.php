<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class ImageStrategy
 */
class ImageStrategy extends AbstractFileAlternativesStrategy
{
    const MIME_TYPE_FRAGMENT_IMAGE = 'image';

    protected $imageManager;
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

        foreach ($this->alternativeFormats as $formatName => $format) {
            $this->generateAlternative($media->getFilesystemName(), $formatName, $format);
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
        $alternativeName = '';
        $alternativePath = $this->imageManager->generateAlternative(
            $this->tmpDir . DIRECTORY_SEPARATOR . $fileName,
            $formatSize
        );

        if ($alternativePath != '') {
            $alternativeName = $this->getAlternativeName($formatName, $fileName);
            $this->uploadedMediaManager->uploadContent($alternativeName, file_get_contents($alternativePath));
            if (trim($alternativePath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
                unlink($alternativePath);
            }
        }

        return $alternativeName;
    }

    /**
     * Return the alternative name
     * 
     * @param string $formatName
     * @param string $fileName
     * 
     * @return string
     */
    protected function getAlternativeName($formatName, $fileName)
    {
        return $formatName . '-' . $fileName;
    }

    /**
     * Delete the thumbnail of $media
     *
     * @param MediaInterface $media
     */
    public function deleteAlternatives(MediaInterface $media)
    {
        foreach ($this->alternativeFormats as $formatName => $format) {
            $this->deleteFile($this->getAlternativeName($formatName, $media->getFilesystemName()));
        }

        parent::deleteAlternatives($media);
    }

    /**
     * Override the file alternative for $media with $newFile and run it
     * 
     * @param MediaInterface $media
     * @param string         $newFilePath
     * @param string         $formatName
     * 
     * @return MediaInterface
     */
    public function overrideAlternative(MediaInterface $media, $newFilePath, $formatName)
    {
        $alternativeName = $this->getAlternativeName($formatName, $media->getFilesystemName());
        $this->deleteFile($alternativeName);
        $this->uploadedMediaManager->uploadContent($alternativeName, file_get_contents($newFilePath));
        unlink($newFilePath);

        return $media;
    }

    /**
     * Crop $media original file with ($x, $y, $h, $w) and resize it to the $formatName
     * 
     * @param MediaInterface $media
     * @param int            $x
     * @param Int            $y
     * @param Int            $h
     * @param Int            $w
     * @param string         $formatName
     * 
     * @return MediaInterface
     */
    public function cropAlternative(MediaInterface $media, $x, $y, $h, $w, $formatName)
    {
        $alternativeName = $this->getAlternativeName($formatName, $media->getFilesystemName());

        $originalFilePath = $this->uploadedMediaManager->downloadFile($media->getFilesystemName(), $this->tmpDir);
        $croppedFilePath = $this->imageManager->cropAndResize($originalFilePath, $x, $y, $h, $w, $formatName);

        $this->deleteFile($alternativeName);
        $this->uploadedMediaManager->uploadContent($alternativeName, file_get_contents($croppedFilePath));

        unlink($originalFilePath);
        unlink($croppedFilePath);

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
