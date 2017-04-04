<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\Media\Manager\MediaStorageManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class PdfStrategy
 */
class PdfStrategy extends AbstractFileAlternativesStrategy
{
    const MEDIA_TYPE = 'pdf';
    const MIME_TYPE_PDF = 'application/pdf';

    protected $imageManager;
    protected $thumbnailFormat;

    /**
     * @param Filesystem                   $fileSystem
     * @param MediaStorageManagerInterface $mediaStorageManager
     * @param ImageManagerInterface        $imageManager
     * @param string                       $tmpDir
     * @param array                        $thumbnailFormat
     */
    public function __construct(
        Filesystem                   $fileSystem,
        MediaStorageManagerInterface $mediaStorageManager,
        ImageManagerInterface        $imageManager,
        $tmpDir,
        array $thumbnailFormat
    ) {
        parent::__construct($fileSystem, $mediaStorageManager, $tmpDir);
        $this->imageManager = $imageManager;
        $this->thumbnailFormat = $thumbnailFormat;
    }

    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media)
    {
        return self::MIME_TYPE_PDF == $media->getMimeType();
    }

    /**
     * Generate a thumbnail for $media
     *
     * @param MediaInterface $media
     */
    public function generateThumbnail(MediaInterface $media)
    {
        $fileName = $media->getFilesystemName();
        $thumbnailName = '';

        $extractedImagePath = $this->imageManager->extractImageFromPdf(
            $this->tmpDir . DIRECTORY_SEPARATOR . $fileName
        );

        $thumbnailPath = $this->imageManager->generateAlternative($extractedImagePath, $this->thumbnailFormat);

        if ($thumbnailPath != '') {
            $thumbnailName = self::THUMBNAIL_PREFIX . '-' . str_replace('.pdf', '.jpg', $fileName);
            $this->mediaStorageManager->uploadFile($thumbnailName, $thumbnailPath);
        }

        if (trim($extractedImagePath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
            $this->fileSystem->remove(array($extractedImagePath));
        }

        $media->setThumbnail($thumbnailName);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pdf_alternatives_strategy';
    }
}
