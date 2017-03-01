<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\MediaAdmin\Event\FolderEvent;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\MediaAdmin\Event\FolderEventFactory;

/**
 * Class UpdateFolderPathSubscriber
 */
class UpdateFolderPathSubscriber implements EventSubscriberInterface
{
    protected $folderRepository;
    protected $eventDispatcher;
    protected $currentSiteManager;
    protected $folderEventFactory;

    /**
     * @param FolderRepositoryInterface $nodeRepository
     * @param EventDispatcherInterface  $eventDispatcher
     * @param CurrentSiteIdInterface    $currentSiteManager
     * @param FolderEventFactory        $folderEventFactory
     */
    public function __construct(
        FolderRepositoryInterface $folderRepository,
        EventDispatcherInterface $eventDispatcher,
        CurrentSiteIdInterface $currentSiteManager,
        FolderEventFactory $folderEventFactory
    ){
        $this->folderRepository = $folderRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->currentSiteManager = $currentSiteManager;
        $this->folderEventFactory = $folderEventFactory;
    }

    /**
     * @param FolderEvent $event
     */
    public function updatePath(FolderEvent $event)
    {
        $folder = $event->getFolder();
        $parent = $folder->getParent();
        $folder->setPath($parent->getPath() . '/' . $folder->getFolderId());

        $siteId = $this->currentSiteManager->getCurrentSiteId();
        $sons = $this->folderRepository->findByParentAndSite($folder->getId(), $siteId);

        foreach ($sons as $son) {
            $event = $this->folderEventFactory->createFolderEvent();
            $event->setFolder($son);
            $this->eventDispatcher->dispatch(FolderEvents::PARENT_UPDATED, $event);
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FolderEvents::PARENT_UPDATED => 'updatePath',
        );
    }
}
