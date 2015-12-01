<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

use Imagick;
use OpenOrchestra\MediaAdmin\Event\ImagickEvent;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickFactory;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\MediaInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ImagickImageManager
 */
class ImagickImageManager implements ImageManagerInterface
{
    protected $compressionQuality;
    protected $dispatcher;
    protected $tmpDir;
    protected $formats;
    protected $imagickFactory;

    /**
     * @param string                   $tmpDir
     * @param array                    $formats
     * @param int                      $compressionQuality
     * @param EventDispatcherInterface $dispatcher
     * @param ImagickFactory           $imagickFactory
     */
    public function __construct(
        $tmpDir,
        array $formats,
        $compressionQuality,
        $dispatcher,
        ImagickFactory $imagickFactory
    ) {
        $this->compressionQuality = $compressionQuality;
        $this->dispatcher = $dispatcher;
        $this->tmpDir = $tmpDir;
        $this->formats = $formats;
        $this->imagickFactory = $imagickFactory;
    }

    /**
     * @param MediaInterface $media
     * @param int            $x
     * @param int            $y
     * @param int            $h
     * @param int            $w
     * @param string         $format
     */
    public function crop(MediaInterface $media, $x, $y, $h, $w, $format)
    {
        $image = $this->imagickFactory->create($this->tmpDir . '/' . $media->getFilesystemName());
        $image->cropImage($w, $h, $x, $y);
        $this->resizeImage($this->formats[$format], $image);

        $this->saveImage($media, $image, $format);
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     */
    public function override(MediaInterface $media, $format)
    {
        $filename = $format . '-' . $media->getFilesystemName();
        $filePath = $this->tmpDir . '/' . $filename;
        $this->resizeAndSaveImage($media, $format, $filePath);
    }

    /**
     * @param MediaInterface $media
     * @param Imagick        $image
     * @param string         $key
     */
    protected function saveImage(MediaInterface $media, Imagick $image, $key)
    {
        $image->setImageCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality($this->compressionQuality);
        $image->stripImage();
        $filename = $key . '-' . $media->getFilesystemName();

        $event = new ImagickEvent($filename, $image);
        $this->dispatcher->dispatch(MediaEvents::RESIZE_IMAGE, $event);
    }

    /**
     * Resize an image keeping its ratio
     *
     * @param array   $format
     * @param Imagick $image
     */
    protected function resizeImage($format, Imagick $image)
    {
        $maxWidth = array_key_exists('max_width', $format)? $format['max_width']: -1;
        $maxHeight = array_key_exists('max_height', $format)? $format['max_height']: -1;

        if ($maxWidth + $maxHeight != -2) {
            $image->setimagebackgroundcolor('#000000');
            $refRatio = $maxWidth / $maxHeight;
            $imageRatio = $image->getImageWidth() / $image->getImageHeight();

            if ($refRatio > $imageRatio || $maxWidth == -1) {
                $this->resizeOnHeight($image, $maxHeight);
            } else {
                $this->resizeOnWidth($image, $maxWidth);
            }
        }
    }

    /**
     * Resize an image keeping its ratio to the width $width
     * 
     * @param Imagick $image
     * @param int     $width
     */
    protected function resizeOnWidth(Imagick $image, $width)
    {
        $image->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * Resize an image keeping its ratio to the height $height
     * 
     * @param Imagick $image
     * @param int     $height
     */
    protected function resizeOnHeight(Imagick $image, $height)
    {
        $image->resizeImage(0, $height, Imagick::FILTER_LANCZOS, 1);
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     * @param string         $filePath
     */
    public function resizeAndSaveImage(MediaInterface $media, $format, $filePath)
    {
        $image = $this->imagickFactory->create($filePath);
        $this->resizeImage($this->formats[$format], $image);

        $this->saveImage($media, $image, $format);
    }

    public function generateAlternative(MediaInterface $media, $formatName, $formatSize)
    {
        $filePath = $this->tmpDir . '/' . $media->getFilesystemName();
        $image = $this->imagickFactory->create($filePath);
        $this->resizeImage($formatSize, $image);

        $this->saveImage($media, $image, $formatName);
    }
}
