<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\EventSubscriber;

use OpenOrchestra\LogBundle\Tests\EventSubscriber\LogAbstractSubscriberTest;
use OpenOrchestra\MediaAdminBundle\EventSubscriber\LogMediaSubscriber;
use Phake;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\MediaAdmin\MediaEvents;

/**
 * Class LogMediaSubscriberTest
 */
class LogMediaSubscriberTest extends LogAbstractSubscriberTest
{
    protected $media;
    protected $folder;
    protected $mediaEvent;
    protected $folderEvent;
    protected $mediaContext;
    protected $folderContext;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        $this->mediaEvent = Phake::mock('OpenOrchestra\MediaAdmin\Event\MediaEvent');
        Phake::when($this->mediaEvent)->getMedia()->thenReturn($this->media);
        $this->folder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        $this->folderEvent = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        Phake::when($this->folderEvent)->getFolder()->thenReturn($this->folder);

        $this->mediaContext = array('media_name' => $this->media->getName());
        $this->folderContext = array('folder_name' => $this->folder->getName());

        $this->subscriber = new LogMediaSubscriber($this->logger);
    }

    /**
     * @return array
     */
    public function provideSubscribedEvent()
    {
        return array(
            array(MediaEvents::MEDIA_ADD),
            array(MediaEvents::MEDIA_UPDATE),
            array(MediaEvents::MEDIA_DELETE),
            array(FolderEvents::FOLDER_CREATE),
            array(FolderEvents::FOLDER_DELETE),
            array(FolderEvents::FOLDER_UPDATE),
        );
    }

    /**
     * Test add image
     */
    public function testAdd()
    {
        $this->subscriber->mediaAdd($this->mediaEvent);
        $this->assertEventLogged('open_orchestra_media_admin.log.media.add', $this->mediaContext);
    }

    /**
     * Test Delete
     */
    public function testDelete()
    {
        $this->subscriber->mediaDelete($this->mediaEvent);
        $this->assertEventLogged('open_orchestra_media_admin.log.media.delete', $this->mediaContext);
    }

    /**
     * Test Delete
     */
    public function testResize()
    {
        $this->subscriber->mediaResize($this->mediaEvent);
        $this->assertEventLogged('open_orchestra_media_admin.log.media.resize', $this->mediaContext);
    }

    /**
     * test folderCreate
     */
    public function testFolderCreate()
    {
        $this->subscriber->folderCreate($this->folderEvent);
        $this->assertEventLogged('open_orchestra_media_admin.log.folder.create', $this->folderContext);
    }

    /**
     * test folderDelete
     */
    public function testFolderDelete()
    {
        $this->subscriber->folderDelete($this->folderEvent);
        $this->assertEventLogged('open_orchestra_media_admin.log.folder.delete', $this->folderContext);
    }

    /**
     * test folderUpdate
     */
    public function testFolderUpdate()
    {
        $this->subscriber->folderUpdate($this->folderEvent);
        $this->assertEventLogged('open_orchestra_media_admin.log.folder.update', $this->folderContext);
    }
}
