<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Transformer;

use Phake;
use OpenOrchestra\ApiBundle\Facade\ModelGroupRoleFacade;
use OpenOrchestra\BackofficeBundle\Model\ModelGroupRoleInterface;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdminBundle\Transformer\MediaFolderGroupRoleTransformer;

/**
 * Class MediaFolderGroupRoleTransformerTest
 */
class MediaFolderGroupRoleTransformerTest extends AbstractBaseTestCase
{
    /**
     * @var MediaFolderGroupRoleTransformer
     */
    protected $transformer;

    protected $facadeClass = 'OpenOrchestra\ApiBundle\Facade\ModelGroupRoleFacade';
    protected $context;
    protected $roleCollector;
    protected $mediaFolderGroupRoleClass;
    protected $folderRepository;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->roleCollector = Phake::mock('OpenOrchestra\Backoffice\Collector\RoleCollectorInterface');
        Phake::when($this->roleCollector)->hasRole(Phake::anyParameters())->thenReturn(true);

        $this->mediaFolderGroupRoleClass = 'OpenOrchestra\GroupBundle\Document\ModelGroupRole';
        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        $this->context = Phake::mock('OpenOrchestra\BaseApi\Transformer\TransformerManager');

        $this->transformer = new MediaFolderGroupRoleTransformer(
            $this->facadeClass,
            $this->mediaFolderGroupRoleClass,
            $this->roleCollector,
            $this->folderRepository
        );
        $this->transformer->setContext($this->context);
    }

    /**
     * Test interface
     */
    public function testInterface()
    {
        $this->assertInstanceOf('OpenOrchestra\ApiBundle\Transformer\TransformerWithGroupInterface', $this->transformer);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('media_folder_group_role', $this->transformer->getName());
    }

   /**
     * @param string $folderId
     * @param string $role
     * @param string $accessType
     * @param bool   $expectedAccess
     * @param bool   $parentAccess
     *
     * @dataProvider provideTransformDataWithAccessType
     */
    public function testReverseTransformGroupWitAccessType($folderId, $role, $accessType, $expectedAccess, $parentAccess)
    {
        $folder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        $source = Phake::mock('OpenOrchestra\BackofficeBundle\Model\ModelGroupRoleInterface');
        $mediaFolderGroupRoleParent = Phake::mock('OpenOrchestra\BackofficeBundle\Model\ModelGroupRoleInterface');

        $facade = $this->createFacade($folderId, $role, $accessType);
        $group = Phake::mock('OpenOrchestra\BackofficeBundle\Model\GroupInterface');
        Phake::when($group)->getModelRoleByTypeAndIdAndRole('folder', $facade->document, $facade->name)->thenReturn($source);
        $parentFolder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($parentFolder)->getId()->thenReturn('fakeId');

        Phake::when($folder)->getParent()->thenReturn($parentFolder);
        Phake::when($this->folderRepository)->find(Phake::anyParameters())->thenReturn($folder);
        Phake::when($group)->getModelRoleByTypeAndIdAndRole('folder', $folder->getParent()->getId(), $facade->name)->thenReturn($mediaFolderGroupRoleParent);
        Phake::when($mediaFolderGroupRoleParent)->isGranted()->thenReturn($parentAccess);

        $mediaFolderGroupRole = $this->transformer->reverseTransformWithGroup($group, $facade, $source);

        $this->assertInstanceOf('OpenOrchestra\BackofficeBundle\Model\ModelGroupRoleInterface', $mediaFolderGroupRole);
        $this->assertSame($source, $mediaFolderGroupRole);
        Phake::verify($source)->setType('folder');
        Phake::verify($source)->setId($folderId);
        Phake::verify($source)->setRole($role);
        Phake::verify($source)->setAccessType($accessType);
        Phake::verify($source)->setGranted($expectedAccess);
    }

    /**
     * @return array
     */
    public function provideTransformDataWithAccessType()
    {
        return array(
            array('foo', 'bar', ModelGroupRoleInterface::ACCESS_GRANTED, true, true),
            array('foo', 'bar', ModelGroupRoleInterface::ACCESS_GRANTED, true, false),
            array('bar', 'foo', ModelGroupRoleInterface::ACCESS_DENIED, false, true),
            array('bar', 'foo', ModelGroupRoleInterface::ACCESS_DENIED, false, false),
            array('bar', 'foo', ModelGroupRoleInterface::ACCESS_INHERIT, false, false),
            array('bar', 'foo', ModelGroupRoleInterface::ACCESS_INHERIT, true, true),
        );
    }
    /**
     * @param string $folder
     * @param string $role
     * @param string $accessType
     * @param bool   $granted
     *
     * @dataProvider provideReverseTransformData
     */
    public function testReverseTransformGroupWithNoExistingData($folder, $role, $accessType, $granted)
    {
        $facade = $this->createFacade($folder, $role, $accessType);
        $group = Phake::mock('OpenOrchestra\BackofficeBundle\Model\GroupInterface');
        Phake::when($group)->getModelRoleByTypeAndIdAndRole('folder', $facade->document, $facade->name)->thenReturn(null);

        $mediaFolderGroupRole = $this->transformer->reverseTransformWithGroup($group, $facade);

        $this->assertInstanceOf('OpenOrchestra\BackofficeBundle\Model\ModelGroupRoleInterface', $mediaFolderGroupRole);
        $this->assertSame($folder, $mediaFolderGroupRole->getId());
        $this->assertSame($role, $mediaFolderGroupRole->getRole());
        $this->assertSame($accessType, $mediaFolderGroupRole->getAccessType());
        $this->assertSame($granted, $mediaFolderGroupRole->isGranted());
    }

    /**
     * @return array
     */
    public function provideReverseTransformData()
    {
        return array(
            array('foo', 'bar', ModelGroupRoleInterface::ACCESS_GRANTED, true),
            array('bar', 'foo', ModelGroupRoleInterface::ACCESS_DENIED, false),
        );
    }

    /**
     * @param string $folder
     * @param string $role
     * @param string $accessType
     *
     * @return ModelGroupRoleFacade
     */
    protected function createFacade($folder, $role, $accessType)
    {
        $facade = Phake::mock('OpenOrchestra\ApiBundle\Facade\ModelGroupRoleFacade');
        $facade->type = 'folder';
        $facade->document = $folder;
        $facade->name = $role;
        $facade->accessType = $accessType;

        return $facade;
    }
}
