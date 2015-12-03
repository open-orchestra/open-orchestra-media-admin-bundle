<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives\Strategy;

use OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class PdfStrategy
 */
class PdfStrategy extends AbstractFileAlternativesStrategy
{
    const MIME_TYPE_PDF = 'application/pdf';

    protected $uploadedMediaManager;
    protected $imageManager;
    protected $thumbnailFormat;

    /**
     * @param UploadedMediaManager  $uploadedMediaManager
     * @param ImageManagerInterface $imageManager
     * @param string                $tmpDir
     * @param array                 $thumbnailFormat
     */
    public function __construct(
        UploadedMediaManager $uploadedMediaManager,
        ImageManagerInterface $imageManager,
        $tmpDir,
        array $thumbnailFormat
    ) {
        $this->uploadedMediaManager = $uploadedMediaManager;
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
     *
     * @return MediaInterface
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
            $this->uploadedMediaManager->uploadContent($thumbnailName, file_get_contents($thumbnailPath));

            if (trim($thumbnailPath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
                unlink($thumbnailPath);
            }
        }

        if (trim($extractedImagePath, DIRECTORY_SEPARATOR) != trim($this->tmpDir, DIRECTORY_SEPARATOR)) {
            unlink($extractedImagePath);
        }

        $media->setThumbnail($thumbnailName);

        return $media;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pdf_alternatives_strategy';
    }
}
