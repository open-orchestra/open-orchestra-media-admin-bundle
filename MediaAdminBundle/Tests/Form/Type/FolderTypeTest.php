<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\FolderType;

/**
 * Class FolderTypeTest
 */
class FolderTypeTest extends AbstractBaseTestCase
{
    protected $form;
    protected $class = 'OpenOrchestra\MediaModelBundle\Document\MediaFolder';
    protected $backLanguages = array('en' => 'English', 'fr' => 'Français');

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->form = new FolderType($this->class, $this->backLanguages);
    }

    /**
     * Test Name
     */
    public function testName()
    {
        $this->assertSame('oo_folder', $this->form->getName());
    }

    /**
     * Test builder
     */
    public function testBuilder()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($builder)->add(Phake::anyParameters())->thenReturn($builder);
        Phake::when($builder)->addEventSubscriber(Phake::anyParameters())->thenReturn($builder);

        $this->form->buildForm($builder, array());

        Phake::verify($builder, Phake::times(1))->add(Phake::anyParameters());
    }

    /**
     * Test the default options
     */
    public function testConfigureOptions()
    {
        $resolverMock = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolverMock);

        Phake::verify($resolverMock)->setDefaults(array(
            'data_class' => $this->class,
            'group_enabled' => true,
            'delete_button' => false,
            'enable_delete_button' => false,
            'delete_help_text' => 'open_orchestra_backoffice.form.folder.delete_help_text',
            'new_button' => false,
            'sub_group_render' => array(
                'property' => array(
                    'rank' => 0,
                    'label' => 'open_orchestra_media_admin.form.folder.sub_group.property',
                )
            )
        ));
    }
}
