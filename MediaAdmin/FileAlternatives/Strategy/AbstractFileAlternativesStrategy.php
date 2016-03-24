<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class AbstractFileAlternativesStrategy
 */
abstract class AbstractFileAlternativesStrategy implements FileAlternativesStrategyInterface
{
    protected $mediaStorageManager;
    protected $tmpDir;
    protected $fileSystem;

    /**
     * Get the $media type supported by the strategy
     *
     * @return srtring
     */
    public function getMediaType() {
        return static::MEDIA_TYPE;
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
