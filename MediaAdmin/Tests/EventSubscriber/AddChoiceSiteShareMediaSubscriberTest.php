<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\Backoffice\SiteFormEvents;
use OpenOrchestra\MediaAdmin\EventSubscriber\AddChoiceSiteShareMediaSubscriber;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;

/**
 * Class AddChoiceSiteShareMediaSubscriberTest
 */
class AddChoiceSiteShareMediaSubscriberTest extends AbstractBaseTestCase
{
    /**
     * @var AddChoiceSiteShareMediaSubscriber
     */
    protected $subscriber;
    protected $mediaLibrarySharingSubscriber;

    /**
     * Set Up the test
     */
    public function setUp()
    {
        $this->mediaLibrarySharingSubscriber = Phake::mock('Symfony\Component\EventDispatcher\EventSubscriberInterface');
        $this->subscriber = new AddChoiceSiteShareMediaSubscriber($this->mediaLibrarySharingSubscriber);
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
        $this->assertArrayHasKey(SiteFormEvents::SITE_FORM_CREATION, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test if method exists
     */
    public function testMethodExists()
    {
        $this->assertTrue(method_exists($this->subscriber, 'addChoiceSite'));
    }

    /**
     * Test add choice site
     */
    public function testAddChoiceSite()
    {
        $event = Phake::mock('OpenOrchestra\Backoffice\Event\SiteFormEvent');
        $builder = Phake::mock('Symfony\Component\Form\FormBuilderInterface');
        Phake::when($event)->getBuilder()->thenReturn($builder);
        Phake::when($builder)->getAttribute('sub_group_render')->thenReturn(array());

        $this->subscriber->addChoiceSite($event);
        Phake::verify($builder)->setAttribute('sub_group_render', array(
            'media' => array(
                'rank' => 2,
                'label' => 'open_orchestra_media_admin.form.site.sub_group.media',
            ))
        );
        Phake::verify($builder)->addEventSubscriber($this->mediaLibrarySharingSubscriber);
    }
}
