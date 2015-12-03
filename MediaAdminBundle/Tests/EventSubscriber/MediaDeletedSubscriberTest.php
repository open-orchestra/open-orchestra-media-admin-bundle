<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\EventSubscriber;

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
    protected $event;
    protected $media;

    /**
     * Set Up the test
     */
    public function setUp()
    {
        $this->media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');

        $this->event = Phake::mock('OpenOrchestra\MediaAdmin\Event\MediaEvent');
        Phake::when($this->event)->getMedia()->thenReturn($this->media);

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
    }

    /**
     * @param string $name
     * @param string $thumbnail
     * @param int    $count
     * @param bool   $exist
     *
     * @dataProvider generateMedia
     */
    public function testDeleteMedia($name, $thumbnail, $count, $exist)
    {
        Phake::when($this->media)->getFilesystemName()->thenReturn($name);
        Phake::when($this->media)->getThumbnail()->thenReturn($thumbnail);

        $this->subscriber->deleteMedia($this->event);

//        Phake::verify($this->uploadedMediaManager, Phake::times(2))->exists(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function generateMedia()
    {
        return array(
            array('image1.jpg.jpg', 'image1.jpg.jpg', 3, true),
            array('pdf1.pdf.pdf', 'pdf1.jpg.jpg', 2, false),
            array('video1.3gp.3gp', 'video1.jpg.jpg', 2, false),
        );
    }
}
