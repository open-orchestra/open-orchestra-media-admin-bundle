<?php

namespace OpenOrchestra\Backoffice\Tests\Form\Type\Component;

use OpenOrchestra\MediaAdminBundle\GeneratePerimeter\Strategy\FolderGeneratePerimeterStrategy;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\Media\Model\MediaFolderInterface;

/**
 * Class FolderGeneratePerimeterStrategyTest
 */
class FolderGeneratePerimeterStrategyTest extends AbstractBaseTestCase
{
    protected $strategy;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $repository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        Phake::when($repository)->findFolderTree(Phake::anyParameters())->thenReturn(
            array (
                array (
                    'folder' =>
                        array (
                            'names' => array('en' => 'Images folder'),
                            'path' => '/images_folder',
                        ),
                    'children' =>
                        array (
                            array (
                                'folder' =>
                                    array (
                                        'names' => array('en' => 'First images folder'),
                                        'path' => '/images_folder/first_images_folder',
                                    ),
                                'children' => array (),
                            ),
                        ),
                ),
                array (
                    'folder' =>
                        array (
                            'names' => array('en' => 'Files folder'),
                            'path' => '/files_folder',
                        ),
                    'children' => array (),
                ),
            )
        );
        $multiChoice = Phake::mock('OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface');
        Phake::when($multiChoice)->choose(array('en' => 'Images folder'))->thenReturn('Images folder');
        Phake::when($multiChoice)->choose(array('en' => 'First images folder'))->thenReturn('First images folder');
        Phake::when($multiChoice)->choose(array('en' => 'Files folder'))->thenReturn('Files folder');
        $this->strategy = new FolderGeneratePerimeterStrategy($repository, $multiChoice);
    }

    /**
     * Test getPerimeterConfiguration
     */
    public function testGetPerimeterConfiguration()
    {
        $result = $this->strategy->getPerimeterConfiguration('siteId');
        $this->assertEquals(array (
            array (
                'root' =>
                    array (
                        'path' => '/images_folder',
                        'name' => 'Images folder',
                    ),
                'children' =>
                    array (
                        array (
                            'root' =>
                                array (
                                  'path' => '/images_folder/first_images_folder',
                                  'name' => 'First images folder',
                                ),
                        ),
                    ),
            ),
            array (
                'root' =>
                    array (
                        'path' => '/files_folder',
                        'name' => 'Files folder',
                    ),
            ),
        ), $result);
    }

    /**
     * Test generatePerimeter
     */
    public function testGeneratePerimeter()
    {
        $result = $this->strategy->generatePerimeter('siteId');
        $this->assertEquals(array (
            '/images_folder',
            '/images_folder/first_images_folder',
            '/files_folder',
        ), $result);
    }

    /**
     * Test getType
     */
    public function testGetType()
    {
        $result = $this->strategy->getType();
        $this->assertEquals(MediaFolderInterface::ENTITY_TYPE, $result);
    }
}
