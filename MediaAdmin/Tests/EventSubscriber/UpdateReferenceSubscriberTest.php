<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\MediaAdmin\EventSubscriber\UpdateReferenceSubscriber;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdmin\MediaEvents;

/**
 * Class UpdateReferenceSubscriberTest
 */
class UpdateReferenceSubscriberTest extends AbstractBaseTestCase
{
    protected $referenceManager;
    protected $objectManager;

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
    }

    /**
     * Test if method exists
     */
    public function testMethodExists()
    {
        $this->assertTrue(method_exists($this->subscriber, 'updateReferencesToMedia'));
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
}
