<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\MediaAdmin\EventSubscriber\MediaCreatedSubscriber;
use OpenOrchestra\MediaAdmin\MediaEvents;
use Phake;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class MediaCreatedSubscriberTest
 */
class MediaCreatedSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected $subscriber;

    protected $fileAlternativesManager;
    protected $documentManager;
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

        $this->documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');

        $this->subscriber = new MediaCreatedSubscriber($this->fileAlternativesManager, $this->documentManager);
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
        $this->assertArrayHasKey(MediaEvents::MEDIA_ADD, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(KernelEvents::TERMINATE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test if method exists
     */
    public function testMethodExists()
    {
        $this->assertTrue(method_exists($this->subscriber, 'addMedia'));
        $this->assertTrue(method_exists($this->subscriber, 'generateAlternatives'));
    }

    /**
     * Test addMedia and generateAlternatives
     */
    public function testGenerateAlternatives()
    {
        $this->subscriber->addMedia($this->event1);
        $this->subscriber->addMedia($this->event2);
        $this->subscriber->generateAlternatives();

        Phake::verify($this->fileAlternativesManager)->generateThumbnail($this->media1);
        Phake::verify($this->fileAlternativesManager)->generateThumbnail($this->media2);
        Phake::verify($this->fileAlternativesManager)->generateAlternatives($this->media1);
        Phake::verify($this->fileAlternativesManager)->generateAlternatives($this->media2);
        Phake::verify($this->documentManager)->flush();
    }

    /**
     * Test not flush, empty medias
     */
    public function testNotFlushEmptyMedia()
    {
        $this->subscriber->generateAlternatives();
        Phake::verify($this->documentManager, Phake::never())->flush();
    }
}
