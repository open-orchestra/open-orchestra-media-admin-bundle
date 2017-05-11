<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\LogBundle\EventSubscriber\AbstractLogSubscriber;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\MediaAdmin\Event\FolderEvent;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class LogMediaSubscriber
 */
class LogMediaSubscriber extends AbstractLogSubscriber
{
    /**
     * @param MediaEvent $event
     */
    public function mediaAdd(MediaEvent $event)
    {
        $this->mediaInfo('open_orchestra_media_admin.log.media.add', $event->getMedia());
    }

    /**
     * @param MediaEvent $event
     */
    public function mediaDelete(MediaEvent $event)
    {
        $this->mediaInfo('open_orchestra_media_admin.log.media.delete', $event->getMedia());
    }

    /**
     * @param MediaEvent $event
     */
    public function mediaUpdate(MediaEvent $event)
    {
        $this->mediaInfo('open_orchestra_media_admin.log.media.resize', $event->getMedia());
    }

    /**
     * @param FolderEvent $event
     */
    public function folderCreate(FolderEvent $event)
    {
        $this->folderInfo('open_orchestra_media_admin.log.folder.create', $event->getFolder());
    }

    /**
     * @param FolderEvent $event
     */
    public function folderDelete(FolderEvent $event)
    {
        $this->folderInfo('open_orchestra_media_admin.log.folder.delete', $event->getFolder());
    }

    /**
     * @param FolderEvent $event
     */
    public function folderUpdate(FolderEvent $event)
    {
        $this->folderInfo('open_orchestra_media_admin.log.folder.update', $event->getFolder());
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            MediaEvents::MEDIA_ADD => 'mediaAdd',
            MediaEvents::MEDIA_UPDATE => 'mediaUpdate',
            MediaEvents::MEDIA_DELETE => 'mediaDelete',
            FolderEvents::FOLDER_CREATE => 'folderCreate',
            FolderEvents::FOLDER_DELETE => 'folderDelete',
            FolderEvents::FOLDER_UPDATE => 'folderUpdate',
        );
    }

    /**
     * @param string         $message
     * @param MediaInterface $media
     */
    protected function mediaInfo($message, MediaInterface $media)
    {
        $this->logger->info($message, array('media_name' => $media->getName()));
    }

    /**
     * @param string          $message
     * @param FolderInterface $folder
     */
    protected function folderInfo($message, FolderInterface $folder)
    {
        $this->logger->info($message, array('folder_name' => $folder->getFolderId()));
    }
}
