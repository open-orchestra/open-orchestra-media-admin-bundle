<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type\Component;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\Component\FolderChoiceType;

/**
 * Class FolderChoiceTypeTest
 */
class FolderChoiceTypeTest extends AbstractBaseTestCase
{
    /**
     * @var FolderChoiceType
     */
    protected $form;

    protected $siteId = 'fakeSiteId';
    protected $language = 'fakeLanguage';


    /**
     * Set up the test
     */
    public function setUp()
    {
        $folders = array (
            array (
                'folder' => array (
                    '_id' => new \MongoId('591ad82908fc4bfa0e8b4593'),
                    'names' =>array ($this->language => 'Images'),
                ),
                'children' => array (
                    array (
                        'folder' =>
                            array (
                                '_id' => new \MongoId('591ad82908fc4bfa0e8b4594'),
                                'names' => array ($this->language => 'Animated Images'),
                            ),
                        'children' => array()
                    )
                )
            )
        );

        $currentSiteManager = Phake::mock('OpenOrchestra\Backoffice\Context\ContextBackOfficeInterface');
        $folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        $folderChoiceTransformer = Phake::mock('Symfony\Component\Form\DataTransformerInterface');

        Phake::when($currentSiteManager)->getSiteId()->thenReturn($this->siteId);
        Phake::when($currentSiteManager)->getBackOfficeLanguage()->thenReturn($this->language);

        Phake::when($folderRepository)->findFolderTree($this->siteId)->thenReturn($folders);

        $this->form = new FolderChoiceType(
            $currentSiteManager,
            $folderRepository,
            $folderChoiceTransformer
        );
    }

    /*
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->form);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('oo_folder_choice', $this->form->getName());
    }

    /**
     * Test add dataTransformer
     */
    public function testBuildForm()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        $this->form->buildForm($builder, array());
        Phake::verify($builder)->addModelTransformer(Phake::anyParameters());
    }

    /**
     * Test configureOptions
     */
    public function testConfigureOptions()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');
        $this->form->configureOptions($resolver);

        Phake::verify($resolver)->setDefaults(
            array(
                'choices' => function() {
                    return ;
                },
                'siteId' => $this->siteId,
                'attr' => array(
                    'class' => 'orchestra-tree-choice'
                )
            )
        );
    }
}
