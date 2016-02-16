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

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->transformer = new FolderTransformer($this->facadeClass);
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
        $this->setExpectedException('OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException');
        $this->transformer->transform(Phake::mock('stdClass'));
    }

    /**
     * @param string          $folderId
     * @param string          $name
     * @param FolderInterface $parent
     * @param array           $sites
     * @param string          $expectedParentId
     *
     * @dataProvider provideTransformData
     */
    public function testTransform($folderId, $name, $parent, array $sites, $expectedParentId)
    {
        $folder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($folder)->getId()->thenReturn($folderId);
        Phake::when($folder)->getName()->thenReturn($name);
        Phake::when($folder)->getParent()->thenReturn($parent);
        Phake::when($folder)->getSites()->thenReturn($sites);

        $facade = $this->transformer->transform($folder);

        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderFacade', $facade);
        $this->assertSame($folderId, $facade->folderId);
        $this->assertSame($name, $facade->name);
        $this->assertSame($expectedParentId, $facade->parentId);
        $this->assertSame($sites, $facade->getSites());
    }

    /**
     * @return array
     */
    public function provideTransformData()
    {
        $parentFolder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($parentFolder)->getId()->thenReturn('FakeParentId');
        $sites = array(array('siteId' => 'FakeSiteId1'), array('siteId' => 'FakeSiteId2'));

        return array(
            array('foo', 'bar', $parentFolder, $sites, 'FakeParentId'),
            array('foo', 'bar', null, $sites, '-'),
        );
    }
}
