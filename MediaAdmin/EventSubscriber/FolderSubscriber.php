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
 * Class FolderSubscriber
 */
class FolderSubscriber implements EventSubscriberInterface
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
        $folder = $event->getFolder();

        $parentFolder = $folder->getParent();
        $site = $this->siteRepository->findOneBySiteId($folder->getSiteId());

        $oldPath = $folder->getPath();
        $newPath = $parentFolder->getPath() . '/' . $folder->getFolderId();
        $folder->setPath($newPath);

        $event = $this->folderEventFactory->createFolderEvent();
        $event->setFolder($folder);
        $event->setPreviousPath($oldPath);
        $this->eventDispatcher->dispatch(FolderEvents::PATH_UPDATED, $event);

        $this->groupRepository->updatePerimeterItem(
            MediaFolderInterface::ENTITY_TYPE,
            $oldPath,
            $newPath,
            $site->getId()
        );

        $children = $this->folderRepository->findByPathAndSite($oldPath, $folder->getSiteId());

        if (count($children) > 0) {
            $childrenId = array();
            foreach ($children as $child) {
                $childOldPath = $child->getPath();
                $childFolderId = $child->getFolderId();
                $child->setPath(preg_replace('/^' . preg_quote($oldPath, '/') . '\//', $newPath . '/', $childOldPath));
                if (!in_array($childFolderId, $childrenId)) {
                    $childrenId[] = $childFolderId;
                    $event = $this->folderEventFactory->createFolderEvent();
                    $event->setFolder($child);
                    $event->setPreviousPath($childOldPath);
                    $this->eventDispatcher->dispatch(FolderEvents::PATH_UPDATED, $event);
                }
                $this->groupRepository->updatePerimeterItem(
                    MediaFolderInterface::ENTITY_TYPE,
                    $childOldPath,
                    $child->getPath(),
                    $site->getId()
                );
            }
        }
    }

    /**
     * @param FolderEvent $event
     */
    public function removeFolderFromPerimeter(FolderEvent $event)
    {
        $folder = $event->getFolder();
        $site = $this->siteRepository->findOneBySiteId($folder->getSiteId());

        $this->groupRepository->removePerimeterItem(
            MediaFolderInterface::ENTITY_TYPE,
            $folder->getPath(),
            $site->getId()
        );
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FolderEvents::FOLDER_MOVE  => 'updatePath',
            FolderEvents::FOLDER_DELETE => 'removeFolderFromPerimeter'
        );
    }
}
