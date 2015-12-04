<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesStrategyInterface;
use OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class AbstractFileAlternativesStrategy
 */
abstract class AbstractFileAlternativesStrategy implements FileAlternativesStrategyInterface
{
    protected $uploadedMediaManager;
    protected $tmpDir;

    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    abstract public function support(MediaInterface $media);

    /**
     * Generate a thumbnail for $media
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    abstract public function generateThumbnail(MediaInterface $media);

    /**
     * Generate all aternatives for $media
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateAlternatives(MediaInterface $media)
    {
        if ($media->getFilesystemName() != '') {
            $filePath = $this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName();
            unlink($filePath);
        }

        return $media;
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
        if ($this->uploadedMediaManager->exists($fileName)) {
            $this->uploadedMediaManager->deleteContent($fileName);
        }
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
        return $media;
    }

    /**
     * @return string
     */
    abstract public function getName();
}
