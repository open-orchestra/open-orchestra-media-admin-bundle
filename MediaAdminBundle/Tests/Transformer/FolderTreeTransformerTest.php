<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Transformer;

use Phake;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdminBundle\Transformer\FolderTreeTransformer;
use OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade;

/**
 * Class FolderTreeTransformerTest
 */
class FolderTreeTransformerTest extends AbstractBaseTestCase
{
    /**
     * @var FolderTreeTransformer
     */
    protected $transformer;
    protected $folderTransformer;
    protected $transformerManager;
    protected $facadeClass = 'OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->transformerManager = Phake::mock('OpenOrchestra\BaseApi\Transformer\TransformerManager');
        $this->folderTransformer = Phake::mock('OpenOrchestra\MediaAdminBundle\Transformer\FolderTransformer');
        Phake::when($this->transformerManager)->get('folder')->thenReturn($this->folderTransformer);
        Phake::when($this->transformerManager)->get('folder_tree')->thenReturn($this->transformer);

        $folderFacade = Phake::mock('OpenOrchestra\MediaAdminBundle\Facade\FolderFacade');
        Phake::when($this->folderTransformer)->transform(Phake::anyParameters())->thenReturn($folderFacade);

        $this->transformer = new FolderTreeTransformer($this->facadeClass);
        $this->transformer->setContext($this->transformerManager);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('folder_tree', $this->transformer->getName());
    }

    /**
     * Test transform FolderInterface
     */
    public function testTransformFolder()
    {
        $folder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        $subFolder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($subFolder)->getSubFolders()->thenReturn(array());
        Phake::when($folder)->getSubFolders()->thenReturn(array($subFolder,$subFolder));

        $facade = $this->transformer->transform($folder);

        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderFacade', $facade->folder);
        $this->verifyFacade($facade, 3);
    }

    /**
     * Test transform multiple root FolderInterface
     */
    public function testTransformRootFolders()
    {
        $rootFolder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        $subFolder = Phake::mock('OpenOrchestra\Media\Model\FolderInterface');
        Phake::when($subFolder)->getSubFolders()->thenReturn(array());
        Phake::when($rootFolder)->getSubFolders()->thenReturn(array($subFolder));
        $rootFolders = array($rootFolder, $rootFolder);

        $facade = $this->transformer->transform($rootFolders);

        $this->assertSame(null , $facade->folder);
        $this->verifyFacade($facade, 4);
    }

    /**
     * @param FolderTreeFacade $facade
     * @param int              $count
     */
    protected function verifyFacade($facade, $count)
    {
        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade', $facade);
        $this->assertSame('children', $facade->collectionName);
        foreach ($facade->getChildren() as $child) {
            $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade', $child);
            $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderFacade', $child->folder);
        }
        Phake::verify($this->folderTransformer, Phake::times($count))->transform(Phake::anyParameters());
    }

    public function testTransformEmpty()
    {
        $folderCollection = array();

        $facade = $this->transformer->transform($folderCollection);

        $this->assertSame($facade, array());
    }
}
