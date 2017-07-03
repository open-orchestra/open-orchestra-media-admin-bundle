<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\MediaAdmin\EventSubscriber\FolderSubscriber;

/**
 * Class FolderSubscriberTest
 */
class FolderSubscriberTest extends AbstractBaseTestCase
{
    protected $subscriber;

    protected $folderRepository;
    protected $eventDispatcher;
    protected $currentSiteManager;
    protected $groupRepository;
    protected $siteId = 'siteId';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');

        $this->eventDispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcher');

        $folderEvent = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        $folderEventFactory = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEventFactory');
        Phake::when($folderEventFactory)->createFolderEvent()->thenReturn($folderEvent);

        $this->groupRepository = Phake::mock('OpenOrchestra\Backoffice\Repository\GroupRepositoryInterface');

        $site = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($site)->getId()->thenReturn($this->siteId);
        $siteRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface');
        Phake::when($siteRepository)->findOneBySiteId(Phake::anyParameters())->thenReturn($site);

        $this->subscriber = new FolderSubscriber(
            $this->folderRepository,
            $this->eventDispatcher,
            $folderEventFactory,
            $this->groupRepository,
            $siteRepository
        );
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    /**
     * Test event subscribed
     */
    public function testEventSubscribed()
    {
        $this->assertArrayHasKey(FolderEvents::FOLDER_MOVE, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(FolderEvents::FOLDER_DELETE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test update path
     */
    public function testUpdatePath()
    {
        $parentFolderId = 'parent';
        $grandParentPath = '/';
        $parentPath = 'parentPath';
        $previousPath = 'previousPath';

        $grandParent = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($grandParent)->getPath()->thenReturn($grandParentPath);

        $parent = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($parent)->getFolderId()->thenReturn($parentFolderId);
        Phake::when($parent)->getPath()->thenReturn($parentPath);
        Phake::when($parent)->getParent()->thenReturn($grandParent);
        Phake::when($parent)->getSiteId()->thenReturn($this->siteId);

        $son1 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        $folderId1 = 'folderId1';
        Phake::when($son1)->getFolderId()->thenReturn($folderId1);
        Phake::when($son1)->getPath()->thenReturn($parentPath . '/' . $folderId1);
        $son2 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        $folderId2 = 'folderId2';
        Phake::when($son2)->getFolderId()->thenReturn($folderId2);
        Phake::when($son2)->getPath()->thenReturn($parentPath . '/' . $folderId2);
        $son3 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        $folderId3 = 'folderId3';
        Phake::when($son3)->getFolderId()->thenReturn($folderId3);
        Phake::when($son3)->getPath()->thenReturn($parentPath . '/' . $folderId3);

        $sons = array($son1, $son2, $son3);

        Phake::when($this->folderRepository)->findByPathAndSite(Phake::anyParameters())->thenReturn($sons);

        $event = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        Phake::when($event)->getFolder()->thenReturn($parent);
        Phake::when($event)->getPreviousPath()->thenReturn($previousPath);

        $this->subscriber->updatePath($event);

        Phake::verify($this->groupRepository)->updatePerimeterItem(
            MediaFolderInterface::ENTITY_TYPE,
            $parentPath,
            $grandParentPath . '/' . $parentFolderId,
            $this->siteId
        );

        Phake::verify($son1)->setPath($grandParentPath . '/' . $parentFolderId . '/' . $folderId1);
        Phake::verify($son2)->setPath($grandParentPath . '/' . $parentFolderId . '/' . $folderId2);
        Phake::verify($son3)->setPath($grandParentPath . '/' . $parentFolderId . '/' . $folderId3);

        Phake::verify($this->eventDispatcher, Phake::times(4))->dispatch(Phake::anyParameters());
    }

    public function testRemoveFolderFromPerimeter()
    {
        $path = 'folderPath';

        $folder = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($folder)->getPath()->thenReturn($path);
        $event = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        Phake::when($event)->getFolder()->thenReturn($folder);

        $this->subscriber->removeFolderFromPerimeter($event);

        Phake::verify($this->groupRepository)->removePerimeterItem(
            MediaFolderInterface::ENTITY_TYPE,
            $path,
            $this->siteId
        );
    }
}
