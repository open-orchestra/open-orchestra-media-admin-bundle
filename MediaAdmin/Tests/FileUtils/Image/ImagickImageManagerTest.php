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

    protected $formats = array(
        'rectangle' => array('max_width' => 100, 'max_height' => 100, 'compression_quality' => 75),
        'fixed_width' => array('max_width' => 100, 'compression_quality' => 75),
        'fixed_height' => array('max_height' => 100, 'compression_quality' => 75)
    );

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->manager = new ImagickImageManager(new ImagickFactory());
    }

    /**
     * @param string $fileName
     * @param string $formatName
     * @param string $expectedFileName
     * @param bool   $skipped
     *
     * @dataProvider provideSize
     */
    public function testGenerateAlternative($filename, $formatName, $expectedFileName, $skipped = false)
    {
        $generatedFile = $this->manager->generateAlternative(
            $this->fixturesPath . 'Source/' . $filename,
            $this->formats[$formatName]
        );

        $this->assertFileCorrectlyGenerated(
            $this->fixturesPath . 'Reference/' . $expectedFileName,
            $generatedFile
        );
    }

    /**
     * @return array
     */
    public function provideSize()
    {
        return array(
             'rectangle' => array('What-are-you-talking-about.jpg', 'rectangle', 'rectangle.jpg'),
             'fixed_width' => array('What-are-you-talking-about.jpg', 'fixed_width', 'fixed-width.jpg', true),
             'fixed_height' => array('What-are-you-talking-about.jpg', 'fixed_height', 'fixed-height.jpg'),
             'fil-hor-rectangle' => array('fil-horizontal.jpg', 'rectangle', 'fil-hor-rectangle.jpg'),
             'fil-hor-fixed_width' => array('fil-horizontal.jpg', 'fixed_width', 'fil-hor-fixed-width.jpg', true),
             'fil-hor-fixed_height' => array('fil-horizontal.jpg', 'fixed_height', 'fil-hor-fixed-height.jpg'),
             'fil-ver-rectangle' => array('fil-vertical.jpg', 'rectangle', 'fil-ver-rectangle.jpg'),
             'fil-ver-fixed_width' => array('fil-vertical.jpg', 'fixed_width', 'fil-ver-fixed-width.jpg', true),
             'fil-ver-fixed_height' => array('fil-vertical.jpg', 'fixed_height', 'fil-ver-fixed-height.jpg')
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
     * @param string $expectedFileName
     *
     * @dataProvider provideCropSize
     */
    public function testCropAndResize($x, $y, $h, $w, $formatName, $expectedFileName)
    {
        $generatedFile = $this->manager->cropAndResize(
            $this->fixturesPath . 'Source/What-are-you-talking-about.jpg', $x, $y, $h, $w, $this->formats[$formatName]
        );

        $this->assertFileCorrectlyGenerated($this->fixturesPath . 'Reference/' . $expectedFileName, $generatedFile);
    }

    /**
     * @return array
     */
    public function provideCropSize()
    {
        return array(
            'rectangle' => array(10, 20, 100, 100, 'rectangle', 'crop-rectangle.jpg'),
            'fixed_width' => array(70, 20, 100, 10, 'fixed_width', 'crop-fixed-width.jpg'),
            'fixed_height' => array(10, 20, 10, 100, 'fixed_height', 'crop-fixed-height.jpg')
        );
    }
}
