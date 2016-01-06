<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\MediaAdmin\EventSubscriber\MediaDeletedSubscriber;
use OpenOrchestra\MediaAdmin\MediaEvents;
use Phake;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class MediaDeletedSubscriberTest
 */
class MediaDeletedSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected $subscriber;

    protected $fileAlternativesManager;
    protected $media1;
    protected $media2;
    protected $event1;
    protected $event2;

    /**
     * Set Up the test
     */
    public function setUp()
    {
        $this->media1 = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        $this->media2 = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');

        $this->event1 = Phake::mock('OpenOrchestra\MediaAdmin\Event\MediaEvent');
        Phake::when($this->event1)->getMedia()->thenReturn($this->media1);

        $this->event2 = Phake::mock('OpenOrchestra\MediaAdmin\Event\MediaEvent');
        Phake::when($this->event2)->getMedia()->thenReturn($this->media2);

        $this->fileAlternativesManager = Phake::mock('OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager');
        $this->subscriber = new MediaDeletedSubscriber($this->fileAlternativesManager);
    }

    /**
     * test instance
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
        $this->assertArrayHasKey(MediaEvents::MEDIA_DELETE, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(KernelEvents::TERMINATE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test if method exists
     */
    public function testMethodExists()
    {
        $this->assertTrue(method_exists($this->subscriber, 'deleteMedia'));
        $this->assertTrue(method_exists($this->subscriber, 'deleteAlternatives'));
    }

    /**
     * Test deleteMedia and deleteAlternatives
     */
    public function testDeleteAlternatives()
    {
        $this->subscriber->deleteMedia($this->event1);
        $this->subscriber->deleteMedia($this->event2);
        $this->subscriber->deleteAlternatives();

        Phake::verify($this->fileAlternativesManager)->deleteThumbnail($this->media1);
        Phake::verify($this->fileAlternativesManager)->deleteThumbnail($this->media2);
        Phake::verify($this->fileAlternativesManager)->deleteAlternatives($this->media1);
        Phake::verify($this->fileAlternativesManager)->deleteAlternatives($this->media2);
    }
}
