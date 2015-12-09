<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Manager;

use Phake;
use OpenOrchestra\MediaAdmin\Manager\ImagickImageManager;

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
    protected $dispatcher;
    protected $compressionQuality;
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
        $this->compressionQuality = 75;
        $this->tmpDir = __DIR__ . '/images';
        $this->formats = array(
            'max_width' => array(
                'max_width' => 100,
            ),
            'max_height' => array(
                'max_height' => 100,
            ),
            'rectangle' => array(
                'width' => 70,
                'height' => 50,
            ),
        );

        $this->dispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media)->getFilesystemName()->thenReturn($this->file);

        $imagickFactory = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickImageManagerOld');
        $imagick = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerOldInterface');
        Phake::when($imagick)->getImageWidth()->thenReturn($this->imageWidth);
        Phake::when($imagick)->getImageHeight()->thenReturn($this->imageHeight);
        Phake::when($imagickFactory)->create(Phake::anyParameters())->thenReturn($imagick);

        $this->manager = new ImagickImageManager(
            $this->tmpDir,
            $this->formats,
            $this->compressionQuality,
            $this->dispatcher,
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
        if (file_exists($this->tmpDir .'/' . $format . '-' . $this->file)) {
            unlink($this->tmpDir .'/' . $format . '-' . $this->file);
        }
        $this->assertFileNotExists($this->tmpDir .'/' . $format . '-' . $this->file);

        $this->manager->crop($this->media, $x, $y, $h, $w, $format);

        Phake::verify($this->dispatcher)->dispatch(Phake::anyParameters());
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
     * Test generate all thumbnails
     */
    public function testGenerateAllThumbnails()
    {
        foreach ($this->formats as $key => $format) {
            if (file_exists($this->tmpDir .'/' . $key . '-' . $this->file)) {
                unlink($this->tmpDir .'/' . $key . '-' . $this->file);
            }
            $this->assertFileNotExists($this->tmpDir .'/' . $key . '-' . $this->file);
        }

        copy($this->tmpDir . '/'. $this->file, $this->tmpDir .'/'. $this->tmpfile);

        Phake::when($this->media)->getFilesystemName()->thenReturn($this->tmpfile);
        $this->manager->generateAllThumbnails($this->media);
        $this->assertFileNotExists($this->tmpDir . '/' . $this->tmpfile);

        Phake::verify($this->dispatcher, Phake::times(3))->dispatch(Phake::anyParameters());
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
        $this->assertFileExists($this->tmpDir . '/' . $fileName);
        Phake::when($this->media)->getFilesystemName()->thenReturn($this->overrideFile);
        $this->manager->override($this->media, $format);
        Phake::verify($this->dispatcher)->dispatch(Phake::anyParameters());
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
