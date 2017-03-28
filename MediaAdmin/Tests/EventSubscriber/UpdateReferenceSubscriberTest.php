<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\MediaAdmin\EventSubscriber\UpdateReferenceSubscriber;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\ModelInterface\ContentTypeEvents;

/**
 * Class UpdateReferenceSubscriberTest
 */
class UpdateReferenceSubscriberTest extends AbstractBaseTestCase
{
    protected $referenceManager;
    protected $objectManager;
    protected $subscriber;

    /**
     * Set Up the test
     */
    public function setUp()
    {
        $this->referenceManager = Phake::mock('OpenOrchestra\Backoffice\Reference\ReferenceManager');
        $this->objectManager = Phake::mock('Doctrine\Common\Persistence\ObjectManager');

        $this->subscriber = new UpdateReferenceSubscriber($this->referenceManager, $this->objectManager);
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
        $this->assertArrayHasKey(ContentTypeEvents::CONTENT_TYPE_CREATE, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(ContentTypeEvents::CONTENT_TYPE_UPDATE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test if method exists
     */
    public function testMethodExists()
    {
        $this->assertTrue(method_exists($this->subscriber, 'updateReferencesToMedia'));
        $this->assertTrue(method_exists($this->subscriber, 'updateReferencesToContentType'));
    }

    /**
     * test updateReferencesToMedia
     */
    public function testUpdateReferencesToMedia()
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        $event = Phake::mock('OpenOrchestra\MediaAdmin\Event\MediaEvent');
        Phake::when($event)->getMedia()->thenReturn($media);
    
        $this->subscriber->updateReferencesToMedia($event);

        Phake::verify($this->referenceManager)->updateReferencesToEntity($media);
    }

    /**
     * test updateReferencesToContentType
     */
    public function testUpdateReferencesToContentType()
    {
        $contentType = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentTypeInterface');
        $event = Phake::mock('OpenOrchestra\ModelInterface\Event\ContentTypeEvent');
        Phake::when($event)->getContentType()->thenReturn($contentType);

        $this->subscriber->updateReferencesToContentType($event);

        Phake::verify($this->referenceManager)->updateReferencesToEntity($contentType);
    }
}
