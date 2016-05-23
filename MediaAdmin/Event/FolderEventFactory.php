<?php

namespace OpenOrchestra\MediaAdmin\Event;

/**
 * Class FolderEventFactory
 */
class FolderEventFactory
{
    /**
     * @return FolderEvent
     */
    public function CreateFolderEvent()
    {
        return new FolderEvent();
    }
}
