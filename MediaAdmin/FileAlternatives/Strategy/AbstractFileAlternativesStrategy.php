<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\Media\Manager\MediaStorageManagerInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;
use OpenOrchestra\Media\Model\MediaInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AbstractFileAlternativesStrategy
 */
abstract class AbstractFileAlternativesStrategy implements FileAlternativesStrategyInterface
{
    protected $mediaStorageManager;
    protected $tmpDir;
    protected $fileSystem;

    /**
     * @param Filesystem                   $fileSystem
     * @param MediaStorageManagerInterface $mediaStorageManager
     * @param string                       $tmpDir
     */
    public function __construct(
        Filesystem $fileSystem,
        MediaStorageManagerInterface $mediaStorageManager,
        $tmpDir
    ) {
        $this->fileSystem = $fileSystem;
        $this->mediaStorageManager = $mediaStorageManager;
        $this->tmpDir = $tmpDir;
    }

    /**
     * Get the $media type supported by the strategy
     *
     * @return string
     */
    public function getMediaType() {
        return static::MEDIA_TYPE;
    }

    /**
     * @param MediaInterface $media
     * @param array          $languages
     */
    public function setMediaInformation(MediaInterface $media, array $languages)
    {
        $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName();
        foreach ($languages as $language) {
            $media->addTitle($language, $media->getName());
        }
        $media->addMediaInformation('size', filesize($filePath));
        $media->addMediaInformation('extension', pathinfo($filePath, PATHINFO_EXTENSION));
    }

    /**
     * Generate all alternatives for $media
     *
     * @param MediaInterface $media
     */
    public function generateAlternatives(MediaInterface $media)
    {
        if ($media->getFilesystemName() != '') {
            $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName();
            $this->fileSystem->remove(array($filePath));
        }
    }

    /**
     * Delete the thumbnail of $media
     *
     * @param MediaInterface $media
     */
    public function deleteThumbnail(MediaInterface $media)
    {
        $this->deleteFile($media->getThumbnail());
    }

    /**
     * Delete all aternatives of $media
     *
     * @param MediaInterface $media
     */
    public function deleteAlternatives(MediaInterface $media)
    {
        $this->deleteFile($media->getFilesystemName());
    }

    /**
     * Remove a media file if it is stored
     * 
     * @param string $fileName
     */
    protected function deleteFile($fileName)
    {
        if (null !== $fileName && $this->mediaStorageManager->exists($fileName)) {
            $this->mediaStorageManager->deleteContent($fileName);
        }
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
    }
}
