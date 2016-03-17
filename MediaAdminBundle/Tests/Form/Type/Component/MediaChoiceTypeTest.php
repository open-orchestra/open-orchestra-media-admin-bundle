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
        Phake::verify($builder, Phake::times(2))->add(Phake::anyParameters());
    }
}
