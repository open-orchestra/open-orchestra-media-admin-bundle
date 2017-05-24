<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\MediaBaseType;

/**
 * Class MediaBaseTypeTest
 */
class MediaBaseTypeTest extends AbstractBaseTestCase
{
    protected $form;
    protected $mediaClass = 'OpenOrchestra\MediaModelBundle\Document\Media';
    protected $frontLanguages = array('fr', 'en');
    protected $contextManager;
    protected $language = 'fr';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->contextManager = Phake::mock('OpenOrchestra\Backoffice\Context\ContextBackOfficeInterface');
        Phake::when($this->contextManager)->getBackOfficeLanguage()->thenReturn($this->language);
        $this->form = new MediaBaseType($this->contextManager, $this->mediaClass, $this->frontLanguages);
    }

    /**
     * Test Name
     */
    public function testName()
    {
        $this->assertSame('oo_media_base', $this->form->getName());
    }

    /**
     * Test builder
     */
    public function testBuilder()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($builder)->add(Phake::anyParameters())->thenReturn($builder);

        $this->form->buildForm($builder, array());

        Phake::verify($builder, Phake::times(4))->add(Phake::anyParameters());
    }

    /**
     * test buildView
     */
    public function testBuildView()
    {
        $formInterface = Phake::mock('Symfony\Component\Form\FormInterface');
        $formView = Phake::mock('Symfony\Component\Form\FormView');
        $options = array(
            'delete_button' => 'fakeValue'
        );

        $this->form->buildView($formView, $formInterface, $options);
        $this->assertEquals('fakeValue', $formView->vars['delete_button']);
        $this->assertEquals(false, $formView->vars['new_button']);
    }

    /**
     * Test the default options
     */
    public function testConfigureOptions()
    {
        $resolverMock = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolverMock);

        Phake::verify($resolverMock)->setDefaults(array(
            'data_class'       => $this->mediaClass,
            'delete_button'    => false,
            'group_enabled'    => true,
            'group_render'     => array(
                'information' => array(
                    'rank'  => 0,
                    'label' => 'open_orchestra_media_admin.form.media.group.information',
                ),
                'usage' => array(
                    'rank'  => 1,
                    'label' => 'open_orchestra_media_admin.form.media.group.usage',
                )
            ),
            'sub_group_render' => array(
                'properties' => array(
                    'rank' => 0,
                    'label' => 'open_orchestra_media_admin.form.media.sub_group.properties',
                ),
            )
        ));
    }
}
