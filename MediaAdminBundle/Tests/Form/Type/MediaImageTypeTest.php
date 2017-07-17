<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\Type;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Form\Type\MediaImageType;

/**
 * Class MediaImageTypeTest
 */
class MediaImageTypeTest extends AbstractBaseTestCase
{
    protected $form;
    protected $mediaClass = 'OpenOrchestra\MediaModelBundle\Document\Media';
    protected $frontLanguages = array('fr', 'en');
    protected $thumbnailConfig = array('format1' => 'params1', 'format2' => 'params2');
    protected $storageManager;
    protected $contextManager;
    protected $language = 'fr';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->storageManager = Phake::mock('OpenOrchestra\Media\Manager\MediaStorageManager');
        $this->contextManager = Phake::mock('OpenOrchestra\Backoffice\Context\ContextBackOfficeInterface');
        Phake::when($this->contextManager)->getBackOfficeLanguage()->thenReturn($this->language);

        $this->form = new MediaImageType(
            $this->contextManager,
            $this->mediaClass,
            $this->frontLanguages,
            $this->thumbnailConfig,
            $this->storageManager
        );
    }

    /**
     * Test Name
     */
    public function testName()
    {
        $this->assertSame('oo_media_image', $this->form->getName());
    }

    /**
     * Test Parent
     */
    public function testParent()
    {
        $this->assertSame('oo_media_base', $this->form->getParent());
    }

    /**
     * Test builder
     */
    public function testBuilder()
    {
        $builder = Phake::mock('Symfony\Component\Form\FormBuilder');
        Phake::when($builder)->add(Phake::anyParameters())->thenReturn($builder);

        $this->form->buildForm($builder, array());

        Phake::verify($builder, Phake::times(6))->add(Phake::anyParameters());
    }

    /**
     * test buildView
     */
    public function testBuildView()
    {
        $formInterface = Phake::mock('Symfony\Component\Form\FormInterface');
        $formView = Phake::mock('Symfony\Component\Form\FormView');
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getAlernative(Phake::anyParameters())->thenReturn('fakeAlternative');
        Phake::when($media)->getAlternatives()->thenReturn(array('fakeAlternative' => 'fakeKey'));
        Phake::when($formInterface)->getData()->thenReturn($media);
        $options = array(
            'delete_button' => 'fakeValue'
        );
        Phake::when($this->storageManager)->getUrl(Phake::anyParameters())->thenReturn('url');

        $this->form->buildView($formView, $formInterface, $options);

        $expectedVars = array('original' => 'url', 'format1' => 'url', 'format2' => 'url');
        $this->assertEquals($expectedVars, $formView->vars['alternatives']);
    }

    /**
     * test buildView without alternatives
     */
    public function testBuildViewWithoutAlternatives()
    {
        $formInterface = Phake::mock('Symfony\Component\Form\FormInterface');
        $formView = Phake::mock('Symfony\Component\Form\FormView');
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getAlternatives()->thenReturn(array());
        Phake::when($formInterface)->getData()->thenReturn($media);

        $this->form->buildView($formView, $formInterface, array());
        $this->assertArrayNotHasKey('alternatives', $formView->vars);
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
            'delete_button' => false,
            'enable_delete_button' => false,
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
                'format' => array(
                    'rank' => 1,
                    'label' => 'open_orchestra_media_admin.form.media.sub_group.format',
                )
            )
        ));
    }
}
