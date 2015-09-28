<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\MediaType;

/**
 * Class MediaTypeTest
 */
class MediaTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MediaType
     */
    protected $form;

    protected $mediaClass = 'site';
    protected $allowedMimeTypes = array('image/*', 'video/*');

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->form = new MediaType($this->mediaClass, $this->allowedMimeTypes);
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
        $this->assertSame('media', $this->form->getName());
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

        Phake::verify($builder)->add('file', 'file', array(
            'label' => 'open_orchestra_media_admin.form.media.file',
            'label_attr' => array('accept' => implode(',', $this->allowedMimeTypes))
        ));
        Phake::verify($builder)->addEventSubscriber(Phake::anyParameters());
    }

    /**
     * Test resolver
     */
    public function testSetDefaultOptions()
    {
        $resolver = Phake::mock('Symfony\Component\OptionsResolver\OptionsResolver');

        $this->form->configureOptions($resolver);

        Phake::verify($resolver)->setDefaults(array(
            'data_class' => $this->mediaClass
        ));
    }
}
