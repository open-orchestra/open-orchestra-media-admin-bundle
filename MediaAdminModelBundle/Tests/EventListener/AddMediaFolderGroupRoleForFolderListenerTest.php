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
     * @param array  $groups
     * @param string $site
     * @param int    $countMFGR
     *
     * @dataProvider provideGroupAndSite
     */
    public function testPostPersist(array $groups,  $site, $countMFGR)
    {
        $folder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($folder)->getSiteId()->thenReturn($site);

        Phake::when($this->lifecycleEventArgs)->getDocument()->thenReturn($folder);
        Phake::when($this->groupRepository)->findAllWithSite()->thenReturn($groups);

        $this->listener->postPersist($this->lifecycleEventArgs);

        Phake::verify($this->documentManager, Phake::times($countMFGR))->persist(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function provideGroupAndSite()
    {
        $group1 = $this->createMockGroup("FakeSiteId1");
        $group2 = $this->createMockGroup("FakeSiteId2");
        $group3 = $this->createMockGroup("FakeSiteId3");
        Phake::when($group3)->hasModelGroupRoleByTypeAndIdAndRole(Phake::anyParameters())->thenReturn(false);

        $site1 = "FakeSiteId1";
        $site2 = "FakeSiteId2";

        return array(
            array(array($group1), $site1, 1),
            array(array($group2), $site2, 1),
            array(array($group3), "", 0),
            array(array($group3), $site2, 0),
            array(array(), $site2, 0),
            array(array(), "", 0),
        );
    }
}
