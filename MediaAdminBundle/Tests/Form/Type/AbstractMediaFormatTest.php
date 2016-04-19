<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;

/**
 * Class AbstractMediaFormatTest
 */
abstract class AbstractMediaFormatTest extends AbstractBaseTestCase
{
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
    protected abstract function testName();


    /**
     * Test build form
     */
    protected abstract function testBuildForm();

    /**
     * Test build view
     */
    public function testBuildView()
    {
        $formView = Phake::mock('Symfony\Component\Form\FormView');
        $formInterface = Phake::mock('Symfony\Component\Form\FormInterface');
        $options = array();

        $this->form->buildView($formView, $formInterface, $options);
        $this->assertEquals($formView->vars['no_submit_button'], true);
        $this->assertEquals($formView->vars['form_legend'], false);
    }
}
