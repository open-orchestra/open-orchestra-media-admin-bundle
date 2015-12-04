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
        $this->media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media)->getFilesystemName()->thenReturn($this->file);

        $imagickFactory = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Image\ImagickFactory');
        $imagick = Phake::mock('Imagick');
        Phake::when($imagick)->getImageWidth()->thenReturn($this->imageWidth);
        Phake::when($imagick)->getImageHeight()->thenReturn($this->imageHeight);
        Phake::when($imagickFactory)->create(Phake::anyParameters())->thenReturn($imagick);

        $this->manager = new ImagickImageManager($imagickFactory);
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

        if (file_exists($format . '-' . $this->file)) {
            unlink($format . '-' . $this->file);
        }
        $this->assertFileNotExists($format . '-' . $this->file);

        $this->manager->crop($this->media, $x, $y, $h, $w, $format);
    }

    /**
     * @return array
     */
    public function provideSize()
    {
        return array(
            array(10, 20, 100, 100, 'rectangle'),
            array(70, 20, 100, 10, 'fixed_width'),
            array(10, 20, 10, 100, 'fixed_height'),
        );
    }
}
