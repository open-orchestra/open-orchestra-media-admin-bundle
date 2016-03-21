<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileUtils\Image;

use OpenOrchestra\MediaAdmin\Tests\FileUtils\AbstractFileUtilsManager;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickFactory;
use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickImageManager;

/**
 * Class ImagickImageManagerTest
 */
class ImagickImageManagerTest extends AbstractFileUtilsManager
{
    /**
     * @var ImagickImageManager
     */
    protected $manager;

    protected $originalFile;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->originalFile = $this->fixturesPath . 'Source/What-are-you-talking-about.jpg';
        $this->manager = new ImagickImageManager(new ImagickFactory());
    }

    /**
     * @param string $formatName
     * @param array  $format
     * @param string $expectedFileName
     * @param bool   $skipped
     *
     * @dataProvider provideSize
     */
    public function testGenerateAlternative($formatName, array $format, $expectedFileName, $skipped = false)
    {
        $generatedFile = $this->manager->generateAlternative($this->originalFile, $format);

        $this->assertFileCorrectlyGenerated($this->fixturesPath . 'Reference/' . $expectedFileName, $generatedFile);
    }

    /**
     * @return array
     */
    public function provideSize()
    {
        return array(
            'max_height' => array('rectangle', array(
                'max_height' => 100, 'max_height' => 100, 'compression_quality' => 75
            ), 'rectangle.jpg'),

            'fixed_width' => array('fixed_width', array(
                'max_width' => 100, 'compression_quality' => 75
            ), 'fixed-width.jpg', true),

            'fixed_height' => array('fixed_height', array(
                'max_height' => 100, 'compression_quality' => 75
            ), 'fixed-height.jpg')
         );
    }

    /**
     * test extractImageFromPdf
     */
    public function testExtractImageFromPdf()
    {
        $generatedFile = $this->manager->extractImageFromPdf($this->fixturesPath . 'Source/BarometreAFUP-Agence-e-2014.pdf');

        $this->assertFileCorrectlyGenerated($this->fixturesPath . 'Reference/pdf.jpg', $generatedFile);
    }

    /**
     * @param int    $x
     * @param int    $y
     * @param int    $h
     * @param int    $w
     * @param string $formatName
     * @param array  $format
     * @param string $expectedFileName
     *
     * @dataProvider provideCropSize
     */
    public function testCropAndResize($x, $y, $h, $w, $formatName, array $format, $expectedFileName)
    {
        $generatedFile = $this->manager->cropAndResize($this->originalFile, $x, $y, $h, $w, $format);
        $this->assertFileCorrectlyGenerated($this->fixturesPath . 'Reference/' . $expectedFileName, $generatedFile);
    }

    /**
     * @return array
     */
    public function provideCropSize()
    {
        return array(
            'rectangle' => array(10, 20, 100, 100, 'rectangle', array(
                'max_height' => 100, 'compression_quality' => 75
            ), 'crop-rectangle.jpg'),

            'fixed_width' => array(70, 20, 100, 10, 'fixed_width', array(
                'max_width' => 100, 'compression_quality' => 75
            ), 'crop-fixed-width.jpg'),

            'fixed_height' => array(10, 20, 10, 100, 'fixed_height', array(
                'max_height' => 100, 'compression_quality' => 75
            ), 'crop-fixed-height.jpg')
        );
    }
}
