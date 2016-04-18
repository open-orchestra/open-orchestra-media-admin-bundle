<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\MediaAdminBundle\Form\Type\MediaSelectFormatType;
use Phake;

/**
 * Class MediaSelectFormatTypeTest
 */
class MediaSelectFormatTypeTest extends AbstractMediaFormatTest
{
    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->form = new MediaSelectFormatType($this->thumbnailConfig);
    }

    /**
     * Test name
     */
    public function testName()
    {
        $this->assertSame('oo_select_format', $this->form->getName());
    }

    /**
     * Test build form
     */
    public function testBuildForm()
    {
        $this->form->buildForm($this->builder, array());

        Phake::verify($this->builder)->add('id', 'hidden');
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
