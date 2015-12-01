<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\EventSubscriber;

use Phake;
use OpenOrchestra\MediaAdmin\EventSubscriber\GenerateImageSubscriber;
use OpenOrchestra\MediaAdmin\MediaEvents;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class GenerateImageSubscriberTest
 */
class GenerateImageSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenerateImageSubscriber
     */
    protected $subscriber;

    protected $event;
    protected $media1;
    protected $media2;
    protected $imagickImageManager;
    protected $file1 = 'What-are-you-talking-about.jpg';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->event = Phake::mock('OpenOrchestra\MediaAdmin\Event\MediaEvent');

        $this->imagickImageManager = Phake::mock('OpenOrchestra\MediaAdmin\Manager\ImagickImageManager');

        $this->media1 = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media1)->getFilesystemName()->thenReturn($this->file1);
        $this->media2 = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');

        $this->subscriber = new GenerateImageSubscriber($this->imagickImageManager);
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
        $this->assertArrayHasKey(MediaEvents::ADD_IMAGE, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(KernelEvents::TERMINATE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test methodes existance
     */
    public function testMethodExists()
    {
        $this->assertTrue(method_exists($this->subscriber, 'addMedia'));
        $this->assertTrue(method_exists($this->subscriber, 'generateImages'));
    }

    /**
     * Test add image
     */
    public function testAddImage()
    {
        Phake::when($this->event)->getMedia()->thenReturn($this->media1);
        $this->subscriber->addMedia($this->event);
        $this->assertCount(1, $this->subscriber->medias);

        Phake::when($this->event)->getMedia()->thenReturn($this->media2);
        $this->subscriber->addMedia($this->event);
        $this->assertCount(2, $this->subscriber->medias);
    }

    /**
     * Test image generation
     */
    public function testGenerateImage()
    {
        $this->subscriber->medias[] = $this->media1;

        $this->subscriber->generateImages();

        Phake::verify($this->imagickImageManager)->generateAllThumbnails($this->media1);
    }
}
