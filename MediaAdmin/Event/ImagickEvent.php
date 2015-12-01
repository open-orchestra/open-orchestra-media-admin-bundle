<?php

namespace OpenOrchestra\MediaAdmin\Event;

use OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface;
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
     * @param ImageManagerInterface $fileContent
     */
    public function __construct($fileName, ImageManagerInterface $fileContent)
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
