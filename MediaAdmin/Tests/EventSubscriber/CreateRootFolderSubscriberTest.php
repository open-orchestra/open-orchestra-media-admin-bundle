<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdmin\EventSubscriber\CreateRootFolderSubscriber;
use OpenOrchestra\ModelInterface\SiteEvents;
use Phake;

/**
 * Class CreateRootFolderSubscriberTest
 */
class CreateRootFolderSubscriberTest extends AbstractBaseTestCase
{

    /** @var CreateRootFolderSubscriber */
    protected $subscriber;
    protected $objectManager;
    protected $translator;
    protected $backLanguages = array('en' => 'English', 'fr' => 'Français');

    /**
     * Set Up the test
     */
    public function setUp()
    {
        $mediaFolderClass = 'OpenOrchestra\MediaModelBundle\Document\MediaFolder';
        $this->objectManager = Phake::mock('Doctrine\Common\Persistence\ObjectManager');
        $this->translator = Phake::mock('Symfony\Component\Translation\TranslatorInterface');

        $this->subscriber = 
            new CreateRootFolderSubscriber($this->objectManager, $this->translator, $mediaFolderClass, $this->backLanguages);
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
        $this->assertArrayHasKey(SiteEvents::SITE_CREATE, $this->subscriber->getSubscribedEvents());
    }

    public function testCreateRootFolder()
    {
        $siteEvent = Phake::mock('OpenOrchestra\ModelInterface\Event\SiteEvent');
        $fakeSiteId = "fakeSiteId";
        $fakeName = "fakeName";
        $site = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($site)->getSiteId()->thenReturn($fakeSiteId);
        Phake::when($siteEvent)->getSite()->thenReturn($site);
        Phake::when($this->translator)->trans(Phake::anyParameters())->thenReturn($fakeName);

        $this->subscriber->createRootFolder($siteEvent);

        Phake::verify($this->objectManager)->persist(Phake::anyParameters());
        Phake::verify($this->objectManager)->flush();
    }
}
