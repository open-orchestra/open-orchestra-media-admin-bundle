<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdmin\EventSubscriber\MediaLibrarySharingSubscriber;
use Phake;
use Symfony\Component\Form\FormEvents;

/**
 * Class MediaLibrarySharingSubscriberTest
 */
class MediaLibrarySharingSubscriberTest extends AbstractBaseTestCase
{
    /**
     * @var MediaLibrarySharingSubscriber
     */
    protected $subscriber;
    protected $event;
    protected $currentSite;
    protected $currentSiteId = 'fakeCurrentSiteId';
    protected $form;
    protected $objectManager;
    protected $siteRepository;
    protected $mediaLibrarySharingRepository;
    protected $mediaLibrarySharingClass = 'OpenOrchestra\MediaModelBundle\Document\MediaLibrarySharing';

    /**
     * Set Up the test
     */
    public function setUp()
    {
        $this->mediaLibrarySharingRepository = Phake::mock('OpenOrchestra\Media\Repository\MediaLibrarySharingRepositoryInterface');
        $this->objectManager = Phake::mock('Doctrine\Common\Persistence\ObjectManager');
        $this->siteRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface');
        $this->event = Phake::mock('Symfony\Component\Form\FormEvent');
        $this->form = Phake::mock('Symfony\Component\Form\FormInterface');
        Phake::when($this->event)->getForm()->thenReturn($this->form);

        $this->currentSite = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($this->currentSite)->getSiteId()->thenReturn($this->currentSiteId);
        Phake::when($this->event)->getData()->thenReturn($this->currentSite);

        $this->subscriber = new MediaLibrarySharingSubscriber(
            $this->mediaLibrarySharingRepository,
            $this->mediaLibrarySharingClass,
            $this->objectManager,
            $this->siteRepository
        );
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
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(FormEvents::POST_SUBMIT, $this->subscriber->getSubscribedEvents());
    }

    /**
     * test pre set data
     */
    public function testPreSetData()
    {
        $fakeSiteId = 'fakeSiteId';

        $mediaLibrarySharing = Phake::mock('OpenOrchestra\Media\Model\MediaLibrarySharingInterface');
        Phake::when($mediaLibrarySharing)->getAllowedSites()->thenReturn(array($fakeSiteId));
        Phake::when($this->mediaLibrarySharingRepository)->findOneBySiteId($this->currentSiteId)->thenReturn($mediaLibrarySharing);

        $site2 = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($site2)->getSiteId()->thenReturn($fakeSiteId);
        Phake::when($site2)->getName()->thenReturn('fakeSiteName');
        Phake::when($this->siteRepository)->findByDeleted(false)->thenReturn(array($site2));

        $this->subscriber->preSetData($this->event);

        Phake::verify($this->form)->add('media_sharing', 'oo_site_choice', array(
            'multiple' => true,
            'expanded' => true,
            'label' => false,
            'required' => false,
            'mapped' => false,
            'choices' => array('fakeSiteName' => $fakeSiteId),
            'group_id' => 'content',
            'sub_group_id' => 'media',
            'data' => array($fakeSiteId)
        ));
    }

    /**
     * Test post submit
     */
    public function testPostSubmit()
    {
        Phake::when($this->form)->isValid()->thenReturn(true);
        Phake::when($this->form)->get('media_sharing')->thenReturn($this->form);
        Phake::when($this->form)->getData()->thenReturn(array('fakeId'));
        $mediaLibrarySharing = Phake::mock('OpenOrchestra\Media\Model\MediaLibrarySharingInterface');
        Phake::when($this->mediaLibrarySharingRepository)->findOneBySiteId($this->currentSiteId)->thenReturn($mediaLibrarySharing);

        $this->subscriber->postSubmit($this->event);

        Phake::verify($mediaLibrarySharing)->setAllowedSites(array('fakeId'));
        Phake::verify($this->objectManager)->persist($mediaLibrarySharing);
        Phake::verify($this->objectManager)->flush();
    }

    /**
     * Test post submit with not valid form
     */
    public function testPostSubmitNotValidForm()
    {
        Phake::when($this->form)->isValid()->thenReturn(false);

        $this->subscriber->postSubmit($this->event);

        Phake::verify($this->objectManager, Phake::never())->persist(Phake::anyParameters());
        Phake::verify($this->objectManager, Phake::never())->flush();
    }
}
