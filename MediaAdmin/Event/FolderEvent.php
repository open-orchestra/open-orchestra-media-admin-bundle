<?php

namespace OpenOrchestra\MediaAdmin\Event;

use OpenOrchestra\Media\Model\FolderInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class FolderEvent
 */
class FolderEvent extends Event
{
    protected $folder;

    /**
     * @return FolderInterface
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param FolderInterface $folder
     */
    public function setFolder(FolderInterface $folder)
    {
        $this->folder = $folder;
    }
}
