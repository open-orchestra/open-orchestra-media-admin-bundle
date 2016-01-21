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
     * Generate all alternatives for $media
     *
     * @param MediaInterface $media
     */
    public function generateAlternatives(MediaInterface $media)
    {
        $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName();

        foreach ($this->alternativeFormats as $formatName => $format) {
            $alternativeName = $this->generateAlternative($media->getFilesystemName(), $formatName, $format);
            $media->addAlternative($formatName, $alternativeName);
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
            $alternativeName = $this->generateAlternativeName($formatName, $fileName);
            $this->mediaStorageManager->uploadFile($alternativeName, $alternativePath);
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
    protected function generateAlternativeName($formatName, $fileName)
    {
        return time(). '-' . $formatName . '-' . $fileName;
    }

    /**
     * Delete the thumbnail of $media
     *
     * @param MediaInterface $media
     */
    public function deleteAlternatives(MediaInterface $media)
    {
        foreach ($media->getAlternatives() as $storageKey) {
            $this->deleteFile($storageKey);
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
        $alternativeName = $media->getAlternative($formatName);
        $this->deleteFile($alternativeName);
        $newFilename = pathinfo($newFilePath, PATHINFO_BASENAME);
        $newAlternativeName = $this->generateAlternativeName($formatName, $newFilename);
        $this->mediaStorageManager->uploadFile($newAlternativeName, $newFilePath);
        $media->addAlternative($formatName, $newAlternativeName);
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
        $originalFilePath = $this->mediaStorageManager->downloadFile($media->getFilesystemName(), $this->tmpDir);
        $tmpFileName = time() . '-' . $media->getName();
        $newFilePath = $this->tmpDir . DIRECTORY_SEPARATOR . $tmpFileName;
        $this->fileSystem->rename($originalFilePath, $newFilePath);

        $croppedFilePath = $this->imageManager
            ->cropAndResize($newFilePath, $x, $y, $h, $w, $this->alternativeFormats[$formatName]);

        $this->overrideAlternative($media, $croppedFilePath, $formatName);

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
