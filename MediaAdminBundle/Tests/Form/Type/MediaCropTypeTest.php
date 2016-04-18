<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\MediaCropType;

/**
 * Class MediaCropTypeTest
 */
class MediaCropTypeTest extends AbstractMediaFormatTest
{
    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->form = new MediaCropType($this->thumbnailConfig);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        parent::testInstance();
        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\Form\Type\MediaSelectFormatType', $this->form);
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
    }
}
