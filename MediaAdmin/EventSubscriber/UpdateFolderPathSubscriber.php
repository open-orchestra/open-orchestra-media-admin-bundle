<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\MediaAdmin\Event\FolderEvent;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\MediaAdmin\Event\FolderEventFactory;

/**
 * Class UpdateFolderPathSubscriber
 */
class UpdateFolderPathSubscriber implements EventSubscriberInterface
{
    protected $folderRepository;
    protected $eventDispatcher;
    protected $folderEventFactory;

    /**
     * @param FolderRepositoryInterface $nodeRepository
     * @param EventDispatcherInterface  $eventDispatcher
     * @param FolderEventFactory        $folderEventFactory
     */
    public function __construct(
        FolderRepositoryInterface $folderRepository,
        EventDispatcherInterface $eventDispatcher,
        FolderEventFactory $folderEventFactory
    ){
        $this->folderRepository = $folderRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->folderEventFactory = $folderEventFactory;
    }

    /**
     * @param FolderEvent $event
     */
    public function updatePath(FolderEvent $event)
    {
        $folder = $event->getFolder();

        $parentPath = '';
        $parent = $folder->getParent();
        if (!is_null($parent)) {
            $parentPath = $parent->getPath();
        }
        $folder->setPath($parentPath . '/' . $folder->getFolderId());

        $sons = $this->folderRepository->findByParentAndSite($folder->getId(), $folder->getSiteId());

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
