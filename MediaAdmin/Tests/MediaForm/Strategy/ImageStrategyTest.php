<?php

namespace OpenOrchestra\MediaAdmin\Tests\MediaForm\Strategy;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdmin\MediaForm\Strategy\ImageStrategy as MediaFormImageStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\ImageStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\VideoStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\AudioStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\PdfStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\DefaultStrategy;
use Phake;

/**
 * Class ImageStrategyTest
 */
class ImageStrategyTest extends AbstractBaseTestCase
{
    protected $strategy;
    protected $alternativeStrategy;
    protected $objectManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->alternativeStrategy = Phake::mock('OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\ImageStrategy');
        $this->objectManager = Phake::mock('Doctrine\Common\Persistence\ObjectManager');
        $tmpDir = 'phakeDire';

        $this->strategy = new MediaFormImageStrategy($this->alternativeStrategy, $this->objectManager, $tmpDir);
    }

    /**
     * Test Instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('OpenOrchestra\MediaAdmin\MediaForm\MediaFormStrategyInterface', $this->strategy);
    }

    /**
     * test support
     * 
     * @param string $mediaType
     * @param bool   $expectedSupport
     * 
     * @dataProvider provideMimeTypes
     */
    public function testSupport($mediaType, $expectedSupport)
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getMediaType()->thenReturn($mediaType);
        $this->assertSame($expectedSupport, $this->strategy->support($media));
    }

    /**
     * Provide Media to check mime types
     */
    public function provideMimeTypes()
    {
        return array(
            array(ImageStrategy::MEDIA_TYPE  , true),
            array(VideoStrategy::MEDIA_TYPE  , false),
            array(AudioStrategy::MEDIA_TYPE  , false),
            array(PdfStrategy::MEDIA_TYPE    , false),
            array(DefaultStrategy::MEDIA_TYPE, false)
        );
    }

    /**
     * test cropAlternative
     *
     * @param array       $crop
     * @param Phake_IMock $file
     * @param int         $cropCount
     * @param int         $overrideCount
     *
     * @dataProvider provideFormData
     */
    public function testCropAlternative(array $crop, $file, $cropCount, $overrideCount)
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        $form = Phake::mock('Symfony\Component\Form\FormInterface');
        Phake::when($form)->getData()->thenReturn($media);

        $this->addChildForm($form, 'x', $crop['x']);
        $this->addChildForm($form, 'y', $crop['y']);
        $this->addChildForm($form, 'h', $crop['h']);
        $this->addChildForm($form, 'w', $crop['w']);
        $this->addChildForm($form, 'format', 'fakeFormat');
        $this->addChildForm($form, 'file', $file);

        $this->strategy->runAdditionalProcess($media, $form);

        Phake::verify($this->alternativeStrategy, Phake::times($cropCount))
            ->cropAlternative($media, $crop['x'], $crop['y'], $crop['h'], $crop['w'], 'fakeFormat');
        Phake::verify($this->objectManager, Phake::times($cropCount + $overrideCount))->persist($media);
        Phake::verify($this->objectManager, Phake::times($cropCount + $overrideCount))->flush();
    }

    /**
     * test overrideAlternative
     *
     * @param array       $crop
     * @param Phake_IMock $file
     * @param int         $cropCount
     * @param int         $overrideCount
     *
     * @dataProvider provideFormData
     */
    public function testOverrideAlternative(array $crop, $file, $cropCount, $overrideCount)
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        $form = Phake::mock('Symfony\Component\Form\FormInterface');
        Phake::when($form)->getData()->thenReturn($media);

        $this->addChildForm($form, 'x', $crop['x']);
        $this->addChildForm($form, 'y', $crop['y']);
        $this->addChildForm($form, 'h', $crop['h']);
        $this->addChildForm($form, 'w', $crop['w']);
        $this->addChildForm($form, 'format', 'fakeFormat');
        $this->addChildForm($form, 'file', $file);

        $this->strategy->runAdditionalProcess($media, $form);

        Phake::verify($this->alternativeStrategy, Phake::times($overrideCount))->overrideAlternative(Phake::anyParameters());
        Phake::verify($this->objectManager, Phake::times($cropCount + $overrideCount))->persist($media);
        Phake::verify($this->objectManager, Phake::times($cropCount + $overrideCount))->flush();
    }

    /**
     * @param Phake_IMock $parentForm
     * @param string      $childName
     * @param string      $childValue
     *
     * @return Phake_IMock
     */
    protected function addChildForm($parentForm, $childName, $childValue)
    {
        $child = Phake::mock('Symfony\Component\Form\FormInterface');
        Phake::when($child)->getData()->thenReturn($childValue);
        Phake::when($parentForm)->get($childName)->thenReturn($child);
    }

    /**
     * @return array
     */
    public function provideFormData()
    {
        $noCropSize = array('x' => null  , 'y' => null, 'h' => null, 'w' => null);
        $cropSize = array('x' => 10  , 'y' => 20, 'h' => 30, 'w' => 40);
        $file = Phake::mock('Symfony\Component\HttpFoundation\File\UploadedFile');

        return array(
            'none'   => array($noCropSize, null , 0, 0),
            'crop'   => array($cropSize, null , 1, 0),
            'upload' => array($noCropSize, $file, 0, 1),
            'both'   => array($cropSize, $file, 1, 1),
        );
    }

    /**
     * test getFormType
     */
    public function testGetFormType()
    {
        $this->assertSame('oo_media_image', $this->strategy->getFormType());
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('image_media_form_strategy', $this->strategy->getName());
    }
}
