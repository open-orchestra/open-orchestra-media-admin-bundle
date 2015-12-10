<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use Symfony\Component\Filesystem\Filesystem;
use OpenOrchestra\MediaFileBundle\Manager\MediaStorageManager;
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
     * @param Filesystem            $fileSystem
     * @param MediaStorageManager   $mediaStorageManager
     * @param ImageManagerInterface $imageManager
     * @param string                $tmpDir
     * @param array                 $thumbnailFormat
     * @param array                 $alternativeFormats
     */
    public function __construct(
        Filesystem $fileSystem,
        MediaStorageManager $mediaStorageManager,
        ImageManagerInterface $imageManager,
        $tmpDir,
        array $thumbnailFormat,
        array $alternativeFormats
    ) {
        $this->fileSystem = $fileSystem;
        $this->mediaStorageManager = $mediaStorageManager;
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
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $thumbnailName = $this->generateAlternative(
            $media->getFilesystemName(),
            self::THUMBNAIL_PREFIX,
            $this->thumbnailFormat
        );

        $media->setThumbnail($thumbnailName);
    }

    /**
     * Generate all aternatives for $media
     *
     * @param MediaInterface $media
     */
    public function generateAlternatives(MediaInterface $media)
    {
        $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName();

        foreach ($this->alternativeFormats as $formatName => $format) {
            $this->generateAlternative($media->getFilesystemName(), $formatName, $format);
        }

        if (trim($filePath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
            $this->fileSystem->remove(array($filePath));
        }
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
            $this->mediaStorageManager->uploadFile($alternativeName, $alternativePath);
        }

        return $alternativeName;
    }

    /**
     * Get alternatives from $media
     * 
     * @param $media
     * 
     * @return array
     */
    public function getAlternatives(MediaInterface $media)
    {
        $alternatives = array();

        foreach ($this->alternativeFormats as $formatName => $format) {
            $alternatives[$formatName] = $this->getAlternativeName($formatName, $media->getFilesystemName());
        }

        return $alternatives;
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
     */
    public function overrideAlternative(MediaInterface $media, $newFilePath, $formatName)
    {
        $alternativeName = $this->getAlternativeName($formatName, $media->getFilesystemName());
        $this->deleteFile($alternativeName);
        $this->mediaStorageManager->uploadFile($alternativeName, $newFilePath);
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
     */
    public function cropAlternative(MediaInterface $media, $x, $y, $h, $w, $formatName)
    {
        $alternativeName = $this->getAlternativeName($formatName, $media->getFilesystemName());

        $originalFilePath = $this->mediaStorageManager->downloadFile($media->getFilesystemName(), $this->tmpDir);
        $croppedFilePath = $this->imageManager
            ->cropAndResize($originalFilePath, $x, $y, $h, $w, $this->alternativeFormats[$formatName]);

        $this->deleteFile($alternativeName);
        $this->mediaStorageManager->uploadFile($alternativeName, $croppedFilePath);

        $this->fileSystem->remove(array($originalFilePath));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image_alternatives_strategy';
    }
}
