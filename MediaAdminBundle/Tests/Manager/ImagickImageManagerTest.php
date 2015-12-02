<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Manager;

use OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickImageManager;
use Phake;

/**
 * Class ImagickImageManagerTest
 */
class ImagickImageManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImagickImageManager
     */
    protected $manager;

    protected $media;
    protected $formats;
    protected $tmpDir;
    protected $file = 'What-are-you-talking-about.jpg';
    protected $imageWidth = 10;
    protected $imageHeight = 10;
    protected $tmpfile = 'tmp-What-are-you-talking-about.jpg';
    protected $overrideFile = 'reference.jpg';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/images';
        $this->formats = array(
            'max_width' => array(
                'max_width' => 100,
                'compression_quality' => 75
            ),
            'max_height' => array(
                'max_height' => 100,
                'compression_quality' => 75
            ),
            'rectangle' => array(
                'width' => 70,
                'height' => 50,
                'compression_quality' => 75
            ),
        );

        $this->media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media)->getFilesystemName()->thenReturn($this->file);

        $imagickFactory = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickFactory');
        $imagick = Phake::mock('Imagick');
        Phake::when($imagick)->getImageWidth()->thenReturn($this->imageWidth);
        Phake::when($imagick)->getImageHeight()->thenReturn($this->imageHeight);
        Phake::when($imagickFactory)->create(Phake::anyParameters())->thenReturn($imagick);

        $this->manager = new ImagickImageManager(
            $this->tmpDir,
            $this->formats,
            $imagickFactory
        );
    }

    /**
     * @param int    $x
     * @param int    $y
     * @param int    $h
     * @param int    $w
     * @param string $format
     *
     * @dataProvider provideSize
     */
    public function testCrop($x, $y, $h, $w, $format)
    {
        $this->markTestSkipped('Refactoring en cours.');

        if (file_exists($this->tmpDir .'/' . $format . '-' . $this->file)) {
            unlink($this->tmpDir .'/' . $format . '-' . $this->file);
        }
        $this->assertFileNotExists($this->tmpDir .'/' . $format . '-' . $this->file);

        $this->manager->crop($this->media, $x, $y, $h, $w, $format);
    }

    /**
     * @return array
     */
    public function provideSize()
    {
        return array(
            array(10, 20, 100, 100, 'rectangle'),
            array(70, 20, 100, 10, 'max_width'),
            array(10, 20, 10, 100, 'max_height'),
        );
    }

    /**
     * Test override
     *
     * @param string $format
     * @param string $fileName
     *
     * @dataProvider generateFormatOverride
     */
    public function testOverride($format, $fileName)
    {
        $this->markTestSkipped('Refactoring en cours.');
        $this->assertFileExists($this->tmpDir . '/' . $fileName);
        Phake::when($this->media)->getFilesystemName()->thenReturn($this->overrideFile);
        $this->manager->override($this->media, $format);
    }

    /**
     * @return array
     */
    public function generateFormatOverride()
    {
        return array(
            array('max_height', 'max_height-' . $this->overrideFile),
            array('max_width', 'max_width-' . $this->overrideFile),
            array('rectangle', 'rectangle-' . $this->overrideFile),
        );
    }
}
