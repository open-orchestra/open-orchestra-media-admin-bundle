<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\MediaCropType;

/**
 * Class MediaCropTypeTest
 */
class MediaCropTypeTest extends AbstractBaseTestCase
{
    /**
     * @var MediaCropType
     */
    protected $form;

    protected $builder;
    protected $thumbnailConfig;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($this->builder)->add(Phake::anyParameters())->thenReturn($this->builder);
        Phake::when($this->builder)->addEventSubscriber(Phake::anyParameters())->thenReturn($this->builder);

        $this->thumbnailConfig = array(
            'rectangle' => array(
                'width' => 100,
                'height' => 100,
            ),
            'fixed_width' => array(
                'max_width' => 100,
            ),
        );

        $this->form = new MediaCropType($this->thumbnailConfig);
    }

    /**
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
        $this->assertSame('oo_media_crop', $this->form->getName());
    }

    /**
     * Test build form
     */
    public function testBuildForm()
    {
        $formView = Phake::mock('Symfony\Component\Form\FormView');
        $formInterface = Phake::mock('Symfony\Component\Form\FormInterface');
        $options = array();

        $this->form->buildForm($this->builder, array());

        Phake::verify($this->builder)->add('id', 'hidden');
        Phake::verify($this->builder)->add('x', 'hidden');
        Phake::verify($this->builder)->add('y', 'hidden');
        Phake::verify($this->builder)->add('h', 'hidden');
        Phake::verify($this->builder)->add('w', 'hidden');
        Phake::verify($this->builder)->add('format', 'choice', array(
            'choices' => array(
                'rectangle' => 'open_orchestra_media_admin.form.media.rectangle',
                'fixed_width' => 'open_orchestra_media_admin.form.media.fixed_width',
            ),
            'label' => 'open_orchestra_media_admin.form.media.format',
            'empty_value' => 'open_orchestra_media_admin.form.media.original_image',
            'required' => false,
        ));

        $this->form->buildView($formView, $formInterface, $options);
        $this->assertEquals($formView->vars['no_submit_button'], true);
        $this->assertEquals($formView->vars['form_legend'], false);
    }
}
