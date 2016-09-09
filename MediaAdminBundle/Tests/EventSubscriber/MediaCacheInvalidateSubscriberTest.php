<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\EventSubscriber\MediaCacheInvalidateSubscriber;
use OpenOrchestra\MediaAdmin\MediaEvents;

/**
 * Class MediaCacheInvalidateSubscriberTest
 */
class MediaCacheInvalidateSubscriberTest extends AbstractBaseTestCase
{
    protected $tagManager;
    protected $cacheableManager;
    protected $subscriber;
    protected $event;
    protected $media;
    protected $mediaId = 'mediaId';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->tagManager = Phake::mock('OpenOrchestra\BaseBundle\Manager\TagManager');

        $this->cacheableManager = Phake::mock('OpenOrchestra\DisplayBundle\Manager\CacheableManager');

        $this->subscriber = new MediaCacheInvalidateSubscriber($this->cacheableManager, $this->tagManager);

        $this->media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media)->getId()->thenReturn($this->mediaId);
        Phake::when($this->media)->getUseReferences(Phake::anyParameters())->thenReturn(array());

        $this->event = Phake::mock('OpenOrchestra\MediaAdmin\Event\MediaEvent');
        Phake::when($this->event)->getMedia()->thenReturn($this->media);
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
        $this->assertArrayHasKey(MediaEvents::MEDIA_UPDATE, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(MediaEvents::MEDIA_DELETE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test methodes existance
     */
    public function testMethodExists()
    {
        $this->assertTrue(method_exists($this->subscriber, 'updateMedia'));
        $this->assertTrue(method_exists($this->subscriber, 'deleteMedia'));
    }

    /**
     * Test updateMedia
     */
    public function testUpdateMedia()
    {
        $this->subscriber->updateMedia($this->event);

        Phake::verify($this->cacheableManager, Phake::times(1))->invalidateTags(Phake::anyParameters());
    }

    /**
     * Test deleteMedia
     */
    public function testDeleteMedia()
    {
        $this->subscriber->deleteMedia($this->event);

        Phake::verify($this->cacheableManager, Phake::times(1))->invalidateTags(Phake::anyParameters());
    }
}
