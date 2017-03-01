<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdmin\EventSubscriber\UpdateFolderPathSubscriber;
use OpenOrchestra\MediaAdmin\FolderEvents;

/**
 * Class UpdateFolderPathSubscriberTest
 */
class UpdateFolderPathSubscriberTest extends AbstractBaseTestCase
{
    protected $subscriber;

    protected $folderRepository;
    protected $eventDispatcher;
    protected $currentSiteManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');

        $this->eventDispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcher');

        $this->currentSiteManager = Phake::mock('OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface');
        Phake::when($this->currentSiteManager)->getCurrentSiteId()->thenReturn('fakeId');

        $folderEvent = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        $folderEventFactory = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEventFactory');
        Phake::when($folderEventFactory)->createFolderEvent()->thenReturn($folderEvent);

        $this->subscriber = new UpdateFolderPathSubscriber(
            $this->folderRepository,
            $this->eventDispatcher,
            $this->currentSiteManager,
            $folderEventFactory
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
        $this->assertArrayHasKey(FolderEvents::PARENT_UPDATED, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test update path
     */
    public function testUpdatePath()
    {
        $siteId = $this->currentSiteManager->getCurrentSiteId();
        $parentFolderId = 'parent';
        $grandParentPath = '/';
        $parentPath = 'parentPath';

        $grandParent = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($grandParent)->getPath()->thenReturn($grandParentPath);

        $parent = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($parent)->getFolderId()->thenReturn($parentFolderId);
        Phake::when($parent)->getPath()->thenReturn($parentPath);
        Phake::when($parent)->getParent()->thenReturn($grandParent);

        $son1 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        $son2 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        $son3 = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');

        $sons = new ArrayCollection();
        $sons->add($son1);
        $sons->add($son2);
        $sons->add($son3);

        Phake::when($this->folderRepository)->findByParentAndSite(Phake::anyParameters())->thenReturn($sons);

        $event = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        Phake::when($event)->getFolder()->thenReturn($parent);

        $this->subscriber->updatePath($event);

        Phake::verify($parent)->setPath($grandParentPath . '/' . $parentFolderId);

        Phake::verify($this->eventDispatcher, Phake::times(3))->dispatch(Phake::anyParameters());
    }
}
