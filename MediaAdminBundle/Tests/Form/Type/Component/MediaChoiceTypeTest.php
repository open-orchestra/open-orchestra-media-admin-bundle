<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type\Component;

use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\Component\MediaChoiceType;

/**
 * Class MediaChoiceTypeTest
 */
class MediaChoiceTypeTest extends \PHPUnit_Framework_TestCase
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
        $this->assertSame('oo_media_choice', $this->form->getName());
    }
    
    /**
     * Test parent
     */
    public function testParent()
    {
        $this->assertSame('text', $this->form->getParent());
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
}
