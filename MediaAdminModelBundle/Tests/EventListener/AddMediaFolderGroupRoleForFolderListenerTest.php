<?php

namespace OpenOrchestra\MediaAdminModelBundle\Tests\EventListener;

use Phake;
use OpenOrchestra\MediaAdminModelBundle\EventListener\AddMediaFolderGroupRoleForFolderListener;

/**
 * Class AddMediaFolderGroupRoleForFolderListenerTest
 */
class AddMediaFolderGroupRoleForFolderListenerTest extends AbstractMediaFolderGroupRoleListenerTest
{
    /**
     * @var AddMediaFolderGroupRoleForFolderListener
     */
    protected $listener;
    protected $groupRepository;
    protected $siteRepository;
    protected $documentManager;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');
        $this->groupRepository = Phake::mock('OpenOrchestra\Backoffice\Repository\GroupRepositoryInterface');
        Phake::when($this->container)->get('open_orchestra_user.repository.group')->thenReturn($this->groupRepository);
        Phake::when($this->lifecycleEventArgs)->getDocumentManager()->thenReturn($this->documentManager);
        $this->siteRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface');
        Phake::when($this->container)->get('open_orchestra_model.repository.site')->thenReturn($this->siteRepository);

        $this->listener = new AddMediaFolderGroupRoleForFolderListener($this->mediaFolderGroupRoleClass);
        $this->listener->setContainer($this->container);
    }

    /**
     * test if the method is callable
     */
    public function testMethodPrePersistCallable()
    {
        $this->assertTrue(method_exists($this->listener, 'postPersist'));
    }

    /**
     * @param array $groups
     * @param int   $expectedPersistTime
     *
     * @dataProvider provideGroupAndSite
     */
    public function testPostPersist(array $groups, $expectedPersistTime)
    {
        $siteId = 'siteId';
        $site = Phake::mock('OpenOrchestra\ModelInterface\Model\SiteInterface');
        Phake::when($site)->getId()->thenReturn($siteId);
        Phake::when($this->siteRepository)->findOneBySiteId(Phake::anyParameters())->thenReturn($site);

        $folder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($folder)->findOneBySiteId(Phake::anyParameters())->thenReturn($site);

        Phake::when($this->lifecycleEventArgs)->getDocument()->thenReturn($folder);
        Phake::when($this->groupRepository)->findAllWithSiteId($siteId)->thenReturn($groups);

        $this->listener->postPersist($this->lifecycleEventArgs);

        Phake::verify($this->documentManager, Phake::times($expectedPersistTime))->persist(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function provideGroupAndSite()
    {
        $groupWithNoGroupRole = $this->createMockGroup("FakeSiteId1");
        $groupWithGroupRole = $this->createMockGroup("FakeSiteId2");
        Phake::when($groupWithNoGroupRole)->hasModelGroupRoleByTypeAndIdAndRole(Phake::anyParameters())->thenReturn(false);
        Phake::when($groupWithGroupRole)->hasModelGroupRoleByTypeAndIdAndRole(Phake::anyParameters())->thenReturn(true);

        return array(
            array(array($groupWithNoGroupRole), 1),
            array(array($groupWithGroupRole), 0),
            array(array($groupWithNoGroupRole, $groupWithGroupRole), 2),
            array(array(), 0),
        );
    }
}
