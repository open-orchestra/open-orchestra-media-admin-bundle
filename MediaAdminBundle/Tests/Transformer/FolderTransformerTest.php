<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Transformer;

use Phake;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\MediaAdminBundle\Transformer\FolderTransformer;

/**
 * Class FolderTransformerTest
 */
class FolderTransformerTest extends AbstractBaseTestCase
{
    /**
     * @var FolderTransformer
     */
    protected $transformer;

    protected $facadeClass = 'OpenOrchestra\MediaAdminBundle\Facade\FolderFacade';
    protected $folderRepository;
    protected $eventDispatcher;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        $this->eventDispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $folderEvent = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEvent');
        $folderEventFactory = Phake::mock('OpenOrchestra\MediaAdmin\Event\FolderEventFactory');
        Phake::when($folderEventFactory)->createFolderEvent()->thenReturn($folderEvent);

        $this->transformer = new FolderTransformer(
            $this->facadeClass,
            $this->folderRepository,
            $this->eventDispatcher,
            $folderEventFactory
        );
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('folder', $this->transformer->getName());
    }

    /**
     * Test with wrong element
     */
    public function testTransformWithWrongElement()
    {
        $this->setExpectedException('OpenOrchestra\BaseApi\Exceptions\TransformerParameterTypeException');
        $this->transformer->transform(Phake::mock('stdClass'));
    }

    /**
     * @param string          $folderId
     * @param string          $name
     * @param FolderInterface $parent
     * @param string          $siteId
     * @param string          $expectedParentId
     *
     * @dataProvider provideTransformData
     */
    public function testTransform($folderId, $name, $parent, $siteId, $expectedParentId)
    {
        $folder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($folder)->getId()->thenReturn($folderId);
        Phake::when($folder)->getName()->thenReturn($name);
        Phake::when($folder)->getParent()->thenReturn($parent);
        Phake::when($folder)->getSiteId()->thenReturn($siteId);

        $facade = $this->transformer->transform($folder);

        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderFacade', $facade);
        $this->assertSame($folderId, $facade->folderId);
        $this->assertSame($name, $facade->name);
        $this->assertSame($expectedParentId, $facade->parentId);
        $this->assertSame($siteId, $facade->siteId);
    }

    /**
     * @return array
     */
    public function provideTransformData()
    {
        $parentFolder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($parentFolder)->getId()->thenReturn('FakeParentId');
        $siteId = 'FakeSiteId1';

        return array(
            array('foo', 'bar', $parentFolder, $siteId, 'FakeParentId'),
            array('foo', 'bar', null, $siteId, '-'),
        );
    }

    /**
     * test reverseTransform
     */
    public function testReverseTransform()
    {
        $facade = Phake::mock('OpenOrchestra\BaseApi\Facade\FacadeInterface');
        $facade->parentId = 'pid';

        $source = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($source)->getFolderId()->thenReturn('folderId');

        $parentFolder = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($parentFolder)->getPath()->thenReturn('parentPath');
        Phake::when($this->folderRepository)->findOneById(Phake::anyParameters())->thenReturn($parentFolder);

        $this->transformer->reverseTransform($facade, $source);
        Phake::verify($source)->setParent($parentFolder);
        Phake::verify($source)->setPath($parentFolder->getPath() . '/' . $source->getFolderId());
        Phake::verify($this->eventDispatcher)->dispatch(Phake::anyParameters());
    }

    /**
     * Provide facade
     *
     * @return array
     */
    public function provideFacade()
    {

        return array(
            array($folder, $parentFolder, 1)
        );
    }
}
