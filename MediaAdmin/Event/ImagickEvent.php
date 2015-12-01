<?php

namespace OpenOrchestra\MediaAdmin\Event;

use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerOldInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ImagickEvent
 */
class ImagickEvent extends Event
{
    protected $fileName;
    protected $fileContent;

    /**
     * @param string                    $fileName
     * @param ImageManagerOldInterface  $fileContent
     */
    public function __construct($fileName, ImageManagerOldInterface $fileContent)
    {
        $this->fileName = $fileName;
        $this->fileContent = $fileContent;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getFileContent()
    {
        return $this->fileContent;
    }
}
