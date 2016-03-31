<?php

namespace OpenOrchestra\MediaAdmin\Tests\Security\Authorization\Voter;

use OpenOrchestra\Media\Model\FolderInterface;
use Phake;
use OpenOrchestra\MediaAdmin\Security\Authorization\Voter\MediaFolderGroupRoleVoter;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class MediaFolderGroupRoleVoterTest
 */
class MediaFolderGroupRoleVoterTest extends AbstractBaseTestCase
{
    /**
     * @var MediaFolderGroupRoleVoter
     */
    protected $voter;
    protected $folderRepository;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        $this->voter = new MediaFolderGroupRoleVoter($this->folderRepository);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Security\Core\Authorization\Voter\VoterInterface', $this->voter);
    }

    /**
     * @param bool   $supports
     * @param string $class
     *
     * @dataProvider provideClassName
     */
    public function testSupportsClass($supports, $class)
    {
        $this->assertSame($supports, $this->voter->supportsClass($class));
    }

    /**
     * @return array
     */
    public function provideClassName()
    {
        return array(
            array(false, 'StdClass'),
            array(false, 'class'),
            array(false, 'string'),
            array(false, 'Symfony\Component\Security\Core\Authorization\Voter\VoterInterface'),
            array(false, 'OpenOrchestra\Backoffice\Model\GroupInterface'),
            array(false, 'OpenOrchestra\ModelInterface\Model\NodeInterface'),
            array(true, 'OpenOrchestra\Media\Model\MediaFolderInterface'),
            array(false, 'OpenOrchestra\ModelInterface\Model\ReadNodeInterface'),
        );
    }

    /**
     * @param string $attribute
     * @param bool   $supports
     *
     * @dataProvider provideAttributeAndSupport
     */
    public function testSupportsAttribute($attribute, $supports)
    {
        $this->assertSame($supports, $this->voter->supportsAttribute($attribute));
    }

    /**
     * @return array
     */
    public function provideAttributeAndSupport()
    {
        return array(
            array('ROLE_ACCESS_TREE_GENERAL_NODE', false),
            array('ROLE_ACCESS_REDIRECTION', false),
            array('ROLE_ACCESS_MEDIA_FOLDER', true),
            array('ROLE_ACCESS_CREATE_MEDIA_FOLDER', true),
            array('ROLE_ACCESS_UPDATE_MEDIA_FOLDER', true),
            array('ROLE_ACCESS_DELETE_MEDIA_FOLDER', true),
            array('ROLE_ACCESS_CREATE_MEDIA', true),
            array('ROLE_ACCESS_UPDATE_MEDIA', true),
            array('ROLE_ACCESS_DELETE_MEDIA', true),
            array('ROLE_ADMIN', false),
            array('ROLE_USER', false),
            array('ROLE_FROM_PUBLISHED_TO_DRAFT', false),
        );
    }

    /**
     * @param int     $expectedVoterResponse
     * @param string  $folderId
     * @param string  $mfgrFolderId
     * @param string  $mfgrRole
     * @param boolean $isGranted
     * @param boolean $isGranted2
     * @param string  $groupSiteId
     *
     * @dataProvider provideResponseAndFolderData
     */
    public function testVote($expectedVoterResponse, $folderId, $mfgrFolderId, $mfgrRole, $isGranted, $isGranted2 = true, $groupSiteId = 'siteId')
    {
        $siteId = 'siteId';
        $role = TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER;
        $folder = Phake::mock('OpenOrchestra\MediaModelBundle\Document\Folder');
        Phake::when($folder)->getId()->thenReturn($folderId);
        Phake::when($folder)->getSiteId()->thenReturn($siteId);

        $mediaFolderGroupRole = Phake::mock('OpenOrchestra\Backoffice\Model\ModelGroupRoleInterface');
        Phake::when($mediaFolderGroupRole)->isGranted()->thenReturn($isGranted);

        $mediaFolderGroupRole2 = Phake::mock('OpenOrchestra\Backoffice\Model\ModelGroupRoleInterface');
        Phake::when($mediaFolderGroupRole2)->isGranted()->thenReturn($isGranted2);

        $group = $this->generateGroup($groupSiteId);
        Phake::when($group)->getModelGroupRoleByTypeAndIdAndRole(FolderInterface::GROUP_ROLE_TYPE, $mfgrFolderId, $mfgrRole)->thenReturn($mediaFolderGroupRole);

        $group2 = $this->generateGroup($groupSiteId);
        Phake::when($group2)->getModelGroupRoleByTypeAndIdAndRole(FolderInterface::GROUP_ROLE_TYPE, $mfgrFolderId, $mfgrRole)->thenReturn($mediaFolderGroupRole2);

        $otherGroup = $this->generateGroup('otherSiteId');
        $noSiteGroup = $this->generateGroup();

        $user = Phake::mock('OpenOrchestra\UserBundle\Model\UserInterface');
        Phake::when($user)->getGroups()->thenReturn(array($noSiteGroup, $otherGroup, $group, $group2));
        $token = Phake::mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        Phake::when($token)->getUser()->thenReturn($user);
        $this->assertSame($expectedVoterResponse, $this->voter->vote($token, $folder, array($role)));
    }

    /**
     * @return array
     */
    public function provideResponseAndFolderData()
    {
        return array(
            'Granted' => array(VoterInterface::ACCESS_GRANTED, 'folderId', 'folderId', TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER, true),
            'Not granted' => array(VoterInterface::ACCESS_DENIED, 'folderId', 'folderId', TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER, false),
            'Different Folder' => array(VoterInterface::ACCESS_DENIED, 'folderId', 'otherId', TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER, true),
            'Different Role' => array(VoterInterface::ACCESS_DENIED, 'folderId', 'folderId', TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER, true),
            'Different Site' => array(VoterInterface::ACCESS_ABSTAIN, 'folderId', 'folderId', TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER, true, true, 'otherSite'),
            'Group2 not granted' => array(VoterInterface::ACCESS_DENIED, 'folderId', 'folderId', TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER, true, false),
        );
    }

    /**
     * @param string $siteId
     *
     * @return mixed
     */
    protected function generateGroup($siteId = null)
    {
        $site = Phake::mock('OpenOrchestra\ModelInterface\Model\ReadSiteInterface');
        Phake::when($site)->getSiteId()->thenReturn($siteId);
        $group = Phake::mock('OpenOrchestra\Backoffice\Model\GroupInterface');
        if (!is_null($siteId)){
            Phake::when($group)->getSite()->thenReturn($site);
        }

        return $group;
    }
}
