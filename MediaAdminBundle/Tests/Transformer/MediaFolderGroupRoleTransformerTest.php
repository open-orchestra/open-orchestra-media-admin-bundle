<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Transformer;

use Phake;
use OpenOrchestra\MediaAdminBundle\Facade\MediaFolderGroupRoleFacade;
use OpenOrchestra\BackofficeBundle\Model\GroupRoleInterface;
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

    protected $facadeClass = 'OpenOrchestra\MediaAdminBundle\Facade\MediaFolderGroupRoleFacade';
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

        $this->mediaFolderGroupRoleClass = 'OpenOrchestra\MediaModelBundle\Document\MediaFolderGroupRole';
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
     * Test with wrong element
     */
    public function testTransformWithWrongElement()
    {
        $this->setExpectedException('OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException');
        $this->transformer->transform(Phake::mock('stdClass'));
    }

    /**
     * @param string $folderId
     * @param string $role
     * @param string $accessType
     *
     * @dataProvider provideTransformData
     */
    public function testTransform($folderId, $role, $accessType)
    {
        $mediaFolderGroupRole = Phake::mock('OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface');
        Phake::when($mediaFolderGroupRole)->getFolderId()->thenReturn($folderId);
        Phake::when($mediaFolderGroupRole)->getRole()->thenReturn($role);
        Phake::when($mediaFolderGroupRole)->getAccessType()->thenReturn($accessType);

        $facade = $this->transformer->transform($mediaFolderGroupRole);

        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\MediaFolderGroupRoleFacade', $facade);
        $this->assertSame($folderId, $facade->folder);
        $this->assertSame($role, $facade->name);
        $this->assertSame($accessType, $facade->accessType);
    }

    /**
     * @return array
     */
    public function provideTransformData()
    {
        return array(
            array('foo', 'bar', GroupRoleInterface::ACCESS_GRANTED),
            array('bar', 'foo', GroupRoleInterface::ACCESS_DENIED),
        );
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
        $source = Phake::mock('OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface');
        $mediaFolderGroupRoleParent = Phake::mock('OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface');

        $facade = $this->createFacade($folderId, $role, $accessType);
        $group = Phake::mock('OpenOrchestra\BackofficeBundle\Model\GroupInterface');
        Phake::when($group)->getMediaFolderRoleByMediaFolderAndRole($facade->folder, $facade->name)->thenReturn($source);
        $parentFolder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($parentFolder)->getId()->thenReturn('fakeId');

        Phake::when($folder)->getParent()->thenReturn($parentFolder);
        Phake::when($this->folderRepository)->find(Phake::anyParameters())->thenReturn($folder);
        Phake::when($group)->getMediaFolderRoleByMediaFolderAndRole($folder->getParent()->getId(), $facade->name)->thenReturn($mediaFolderGroupRoleParent);
        Phake::when($mediaFolderGroupRoleParent)->isGranted()->thenReturn($parentAccess);

        $mediaFolderGroupRole = $this->transformer->reverseTransformWithGroup($group, $facade, $source);

        $this->assertInstanceOf('OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface', $mediaFolderGroupRole);
        $this->assertSame($source, $mediaFolderGroupRole);
        Phake::verify($source)->setFolderId($folderId);
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
            array('foo', 'bar', GroupRoleInterface::ACCESS_GRANTED, true, true),
            array('foo', 'bar', GroupRoleInterface::ACCESS_GRANTED, true, false),
            array('bar', 'foo', GroupRoleInterface::ACCESS_DENIED, false, true),
            array('bar', 'foo', GroupRoleInterface::ACCESS_DENIED, false, false),
            array('bar', 'foo', GroupRoleInterface::ACCESS_INHERIT, false, false),
            array('bar', 'foo', GroupRoleInterface::ACCESS_INHERIT, true, true),
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
        Phake::when($group)->getMediaFolderRoleByMediaFolderAndRole($facade->folder, $facade->name)->thenReturn(null);

        $mediaFolderGroupRole = $this->transformer->reverseTransformWithGroup($group, $facade);

        $this->assertInstanceOf('OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface', $mediaFolderGroupRole);
        $this->assertSame($folder, $mediaFolderGroupRole->getFolderId());
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
            array('foo', 'bar', GroupRoleInterface::ACCESS_GRANTED, true),
            array('bar', 'foo', GroupRoleInterface::ACCESS_DENIED, false),
        );
    }

    /**
     * Throw exception when role not found
     */
    public function testWithNonExistingRole()
    {
        $facade = Phake::mock('OpenOrchestra\MediaAdminBundle\Facade\MediaFolderGroupRoleFacade');
        $group = Phake::mock('OpenOrchestra\BackofficeBundle\Model\GroupInterface');
        Phake::when($this->roleCollector)->hasRole(Phake::anyParameters())->thenReturn(false);

        $this->setExpectedException('OpenOrchestra\ApiBundle\Exceptions\HttpException\RoleNotFoundHttpException');

        $this->transformer->reverseTransformWithGroup($group, $facade);
    }

    /**
     * @param string $folder
     * @param string $role
     * @param string $accessType
     *
     * @return MediaFolderGroupRoleFacade
     */
    protected function createFacade($folder, $role, $accessType)
    {
        $facade = Phake::mock('OpenOrchestra\MediaAdminBundle\Facade\MediaFolderGroupRoleFacade');
        $facade->folder = $folder;
        $facade->name = $role;
        $facade->accessType = $accessType;

        return $facade;
    }
}
