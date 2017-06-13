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
    protected $folderFacadeClass = 'OpenOrchestra\MediaAdminBundle\Facade\FolderFacade';
    protected $multiLanguageChoiceManager;

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

        $this->multiLanguageChoiceManager = Phake::mock('OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface');

        $autorizationChecker = Phake::mock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $this->transformer = new FolderTreeTransformer(
            $this->facadeClass,
            $this->folderFacadeClass,
            $autorizationChecker,
            $this->multiLanguageChoiceManager
        );
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
     *
     * @param array $folderCollection
     *
     * @dataProvider provideTree
     */
    public function testTransform(array $folderCollection)
    {
        $facade = $this->transformer->transform($folderCollection);

        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade', $facade);
        $this->assertSame(null, $facade->folder);
        $this->assertSame('children', $facade->collectionName);
        foreach ($facade->getChildren() as $child) {
            $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade', $child);
            $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Facade\FolderFacade', $child->folder);
        }
    }

    /**
     * @return array
     */
    public function provideTree()
    {
        $rootFolder = $this->generateFolder(array('en' => 'Root folder'), 'rootId', 'rootFolderId', 'rootType', '2');
        $subFolder  = $this->generateFolder(array('en' => 'Sub folder') , 'subId' , 'subFolderId' , 'rootType', '3');

        $folderCollection1 = array(
            array(
                'folder'   => $rootFolder,
                'children' => array(
                    array('folder' => $subFolder)
                )
            )
        );

        $folderCollection2 = array(
            array(
                'folder'   => $rootFolder,
                'children' => array(
                    array('folder' => $subFolder),
                    array('folder' => $subFolder)
                )
            )
        );

        return array(
            array(array()),
            array($folderCollection1),
            array($folderCollection2),
        );
    }

    /**
     * @param string $name
     * @param string $id
     * @param string $folderId
     * @param string $type
     * @param string $siteId
     *
     * @return array
     */
    protected function generateFolder(array $names, $id, $folderId, $type, $siteId)
    {
        return array(
            'names'    => $names,
            '_id'      => $id,
            'folderId' => $folderId,
            'type'     => $type,
            'siteId'   => $siteId
        );
    }
}
