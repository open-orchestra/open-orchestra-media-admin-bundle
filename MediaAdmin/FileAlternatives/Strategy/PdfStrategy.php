<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaFileBundle\Manager\MediaStorageManager;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class PdfStrategy
 */
class PdfStrategy extends AbstractFileAlternativesStrategy
{
    const MIME_TYPE_PDF = 'application/pdf';

    protected $imageManager;
    protected $thumbnailFormat;

    /**
     * @param MediaStorageManager   $mediaStorageManager
     * @param ImageManagerInterface $imageManager
     * @param string                $tmpDir
     * @param array                 $thumbnailFormat
     */
    public function __construct(
        MediaStorageManager $mediaStorageManager,
        ImageManagerInterface $imageManager,
        $tmpDir,
        array $thumbnailFormat
    ) {
        $this->mediaStorageManager = $mediaStorageManager;
        $this->imageManager = $imageManager;
        $this->tmpDir = $tmpDir;
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
            unlink($extractedImagePath);
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
