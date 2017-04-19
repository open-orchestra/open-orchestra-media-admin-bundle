<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdmin\EventSubscriber\UpdateFolderPathSubscriber;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\Media\Model\MediaFolderInterface;

/**
 * Class UpdateFolderPathSubscriberTest
 */
class UpdateFolderPathSubscriberTest extends AbstractBaseTestCase
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

        $this->subscriber = new UpdateFolderPathSubscriber(
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
        $this->assertArrayHasKey(FolderEvents::PATH_UPDATED, $this->subscriber->getSubscribedEvents());
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
        $son2 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        $folderId2 = 'folderId2';
        Phake::when($son2)->getFolderId()->thenReturn($folderId2);
        $son3 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        $folderId3 = 'folderId3';
        Phake::when($son3)->getFolderId()->thenReturn($folderId3);

        $sons = array($son1, $son2, $son3);

        Phake::when($this->folderRepository)->findByParentAndSite(Phake::anyParameters())->thenReturn($sons);

        $event = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        Phake::when($event)->getFolder()->thenReturn($parent);
        Phake::when($event)->getPreviousPath()->thenReturn($previousPath);

        $this->subscriber->updatePath($event);

        Phake::verify($this->groupRepository)->updatePerimeterItem(
            MediaFolderInterface::ENTITY_TYPE,
            $previousPath,
            $parentPath,
            $this->siteId
        );

        Phake::verify($son1)->setPath($parentPath . '/' . $folderId1);
        Phake::verify($son2)->setPath($parentPath . '/' . $folderId2);
        Phake::verify($son3)->setPath($parentPath . '/' . $folderId3);

        Phake::verify($this->eventDispatcher, Phake::times(3))->dispatch(Phake::anyParameters());
    }
}
