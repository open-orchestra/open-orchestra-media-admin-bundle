<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type\Component;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\Component\MediaChoiceType;

/**
 * Class MediaChoiceTypeTest
 */
class MediaChoiceTypeTest extends AbstractBaseTestCase
{
    /**
     * @var MediaChoiceType
     */
    protected $form;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->form = new MediaChoiceType();
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
        $this->assertSame('oo_media_choice', $this->form->getName());
    }

    /**
     * Test add dataTransformer
     */
    public function testBuildForm()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($builder)->add(Phake::anyParameters())->thenReturn($builder);

        $this->form->buildForm($builder, array());

        Phake::verify($builder)->addModelTransformer(Phake::anyParameters());
        Phake::verify($builder, Phake::times(4))->add(Phake::anyParameters());
    }

    /**
     * Test configureOptions
     */
    public function testConfigureOptions()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolver);

        Phake::verify($resolver)->setDefaults(array('filter' => ''));
    }

    /**
     * Test finishView
     *
     * @param array  $options
     * @param string $expectedFilter
     *
     * @dataProvider provideOptions
     */
    public function testFinishView(array $options, $expectedFilter)
    {
        $view = Phake::mock('Symfony\Component\Form\FormView');
        $view->vars = array();
        $form = Phake::mock('Symfony\Component\Form\FormInterface');

        $this->form->finishView($view, $form, $options);

        $this->assertArrayHasKey('filter',$view->vars);
        $this->assertSame($expectedFilter, $view->vars['filter']);
    }

    /**
     * Provide options
     */
    public function provideOptions()
    {
        return array(
            'noFilter'=> array(array(), ''),
            'someFilter' => array(array('filter' => 'someFilter'), 'someFilter')
        );
    }
}
