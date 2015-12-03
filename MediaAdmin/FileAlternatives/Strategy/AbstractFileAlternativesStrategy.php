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
     * @return string
     */
    abstract public function getName();
}
