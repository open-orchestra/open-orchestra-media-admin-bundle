<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\MediaAdmin\Event\FolderEvent;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\MediaAdmin\Event\FolderEventFactory;
use OpenOrchestra\Backoffice\Repository\GroupRepositoryInterface;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;

/**
 * Class UpdateFolderPathSubscriber
 */
class UpdateFolderPathSubscriber implements EventSubscriberInterface
{
    protected $folderRepository;
    protected $eventDispatcher;
    protected $folderEventFactory;
    protected $groupRepository;
    protected $siteRepository;

    /**
     * @param FolderRepositoryInterface $nodeRepository
     * @param EventDispatcherInterface  $eventDispatcher
     * @param FolderEventFactory        $folderEventFactory
     * 
     */
    public function __construct(
        FolderRepositoryInterface $folderRepository,
        EventDispatcherInterface $eventDispatcher,
        FolderEventFactory $folderEventFactory,
        GroupRepositoryInterface $groupRepository,
        SiteRepositoryInterface $siteRepository
    ) {
        $this->folderRepository = $folderRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->folderEventFactory = $folderEventFactory;
        $this->groupRepository = $groupRepository;
        $this->siteRepository = $siteRepository;
    }

    /**
     * @param FolderEvent $event
     */
    public function updatePath(FolderEvent $event)
    {
        $parentFolder = $event->getFolder();
        $site = $this->siteRepository->findOneBySiteId($parentFolder->getSiteId());

        $this->groupRepository->updatePerimeterItem(
            MediaFolderInterface::ENTITY_TYPE,
            $event->getPreviousPath(),
            $parentFolder->getPath(),
            $site->getId()
        );

        $folders = $this->folderRepository->findByParentAndSite($parentFolder->getId(), $parentFolder->getSiteId());
        if (is_array($folders)) {
            foreach ($folders as $folder) {
                $oldPath = $folder->getPath();
                $folder->setPath($parentFolder->getPath() . '/' . $folder->getFolderId());

                $event = $this->folderEventFactory->createFolderEvent();
                $event->setFolder($folder);
                $event->setPreviousPath($oldPath);
                $this->eventDispatcher->dispatch(FolderEvents::PATH_UPDATED, $event);
            }
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
