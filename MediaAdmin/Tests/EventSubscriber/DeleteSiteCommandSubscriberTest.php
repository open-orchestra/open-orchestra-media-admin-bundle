<?php

namespace OpenOrchestra\MediaAdmin\Tests\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\Backoffice\SiteCommandEvents;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdmin\EventSubscriber\DeleteSiteCommandSubscriber;
use Phake;

/**
 * Class DeleteSiteCommandSubscriberTest
 */
class DeleteSiteCommandSubscriberTest extends AbstractBaseTestCase
{

    /** @var DeleteSiteCommandSubscriber */
    protected $subscriber;
    protected $mediaClass = 'OpenOrchestra\MediaModelBundle\Document\Media';
    protected $mediaRepository;
    protected $folderRepository;
    protected $deleteSiteTools;
    protected $io;
    protected $event;

    /**
     * Set Up the test
     */
    public function setUp()
    {
        $this->mediaRepository = Phake::mock('OpenOrchestra\Media\Repository\MediaRepositoryInterface');
        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        $this->deleteSiteTools = Phake::mock('OpenOrchestra\Backoffice\Command\OrchestraDeleteSiteTools');
        $this->io = Phake::mock('Symfony\Component\Console\Style\SymfonyStyle');
        $this->event = Phake::mock('OpenOrchestra\Backoffice\Event\SiteCommandEvent');
        Phake::when($this->event)->getIo()->thenReturn($this->io);

        $this->subscriber = new DeleteSiteCommandSubscriber(
            $this->mediaRepository,
            $this->folderRepository,
            $this->deleteSiteTools,
            $this->mediaClass
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
        $this->assertArrayHasKey(SiteCommandEvents::SITE_CHECK_HARD_DELETE, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(SiteCommandEvents::SITE_HARD_DELETE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test chech hard delete site
     */
    public function testCheckHardDeleteSite()
    {
        $siteId = 'fakeSiteId';
        $site = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($site)->getSiteId()->thenReturn($siteId);
        Phake::when($this->event)->getSite()->thenReturn($site);

        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        $medias = new ArrayCollection(array($media));
        Phake::when($this->mediaRepository)->findWithUseReferences(Phake::anyParameters())->thenReturn($medias);
        Phake::when($this->deleteSiteTools)->findUsageReferenceInOtherSite(Phake::anyParameters())->thenReturn(array(
            'entity' => $media,
            'references' => array()
        ));

        $this->expectException('\RuntimeException');
        $this->subscriber->checkHardDeleteSite($this->event);
        Phake::verify($this->mediaRepository)->findWithUseReferences($siteId);
        Phake::verify($this->deleteSiteTools)->findUsageReferenceInOtherSite($siteId, $medias);
        Phake::verify($this->deleteSiteTools)->displayUsedReferences(Phake::anyParameters());
        Phake::verify($this->io)->section('Usage of media in other sites');
    }

    /**
     * Test delete media
     */
    public function testDeleteMedia()
    {
        $siteId = 'fakeSiteId';
        $site = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($site)->getSiteId()->thenReturn($siteId);
        Phake::when($this->event)->getSite()->thenReturn($site);

        $this->subscriber->deleteMedia($this->event);
        Phake::verify($this->io)->comment('Remove use references of medias');
        Phake::verify($this->deleteSiteTools)->removeUseReferenceEntity($siteId, $this->mediaClass);

        Phake::verify($this->io)->comment('Remove medias');
        Phake::verify($this->mediaRepository)->removeAllBySiteId($siteId);

        Phake::verify($this->io)->comment('Remove folders');
        Phake::verify($this->folderRepository)->removeAllBySiteId($siteId);
    }
}
