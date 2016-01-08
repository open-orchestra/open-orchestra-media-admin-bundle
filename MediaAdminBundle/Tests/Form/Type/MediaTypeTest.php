<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\MediaType;
use Symfony\Component\Validator\Constraints\File;

/**
 * Class MediaTypeTest
 */
class MediaTypeTest extends AbstractBaseTestCase
{
    /**
     * @var MediaType
     */
    protected $form;

    protected $mediaClass = 'site';
    protected $allowedMimeTypes = array('image/jpeg', 'video/mpeg');

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
        $this->assertSame('oo_media', $this->form->getName());
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
            'label_attr' => array('accept' => implode(',', $this->allowedMimeTypes)),
            'constraints' => array(new File(array(
                    'mimeTypes' => $this->allowedMimeTypes
            )))
        ));
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
