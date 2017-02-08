<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\MediaAdmin\Event\FolderEvent;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;

/**
 * Class UpdateFolderPathSubscriber
 */
class UpdateFolderPathSubscriber implements EventSubscriberInterface
{
    protected $folderRepository;
    protected $eventDispatcher;
    protected $currentSiteManager;

    /**
     * @param FolderRepositoryInterface  $nodeRepository
     * @param EventDispatcherInterface $eventDispatcher
     * @param CurrentSiteIdInterface   $currentSiteManager
     */
    public function __construct(
        FolderRepositoryInterface $folderRepository,
        EventDispatcherInterface $eventDispatcher,
        CurrentSiteIdInterface $currentSiteManager
    ){
        $this->folderRepository = $folderRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->currentSiteManager = $currentSiteManager;
    }

    /**
     * @param FolderEvent $event
     */
    public function updatePath(FolderEvent $event)
    {
        $folder = $event->getFolder();
        $parentPath = $folder->getPath();
        $siteId = $this->currentSiteManager->getCurrentSiteId();
        $sons = $this->folderRepository->findByParentAndSite($folder->getId(), $siteId);

        $sonsToUpdate = array();
        foreach ($sons as $son) {
            $son->setPath($parentPath . '/' . $son->getFolderId());
            $sonsToUpdate[$son->getFolderId()] = $son;
        }

        foreach ($sonsToUpdate as $sonToUpdate) {
            $event = new FolderEvent($sonToUpdate);
            $this->eventDispatcher->dispatch(FolderEvents::PATH_UPDATED, $event);
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FolderEvents::PATH_UPDATED => 'updatePath',
        );
    }
}
