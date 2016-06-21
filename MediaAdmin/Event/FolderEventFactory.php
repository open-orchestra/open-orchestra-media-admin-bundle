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
    public function createFolderEvent()
    {
        return new FolderEvent();
    }
}
