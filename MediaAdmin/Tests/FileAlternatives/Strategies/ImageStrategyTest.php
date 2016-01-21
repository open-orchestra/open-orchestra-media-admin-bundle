<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\ImageStrategy;
use Phake;

/**
 * Class ImageStrategyTest
 */
class ImageStrategyTest extends AbstractFileAlternativesStrategy
{
    protected $thumbnail = 'default.jpg';
    protected $imageManager;
    protected $thumbnailFormat;
    protected $alternativesFormats;

    protected $generatedFilePath = 'generated.jpg';

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        Phake::when($this->mediaStorageManager)->exists('format1-' . $this->fullMediaFileSystemName)->thenReturn(true);
        Phake::when($this->mediaStorageManager)->exists('format2-' . $this->fullMediaFileSystemName)->thenReturn(true);
        Phake::when($this->mediaStorageManager)->exists('format1-' . $this->emptyMediaFileSystemName)->thenReturn(false);
        Phake::when($this->mediaStorageManager)->exists('format2-' . $this->emptyMediaFileSystemName)->thenReturn(false);

        $this->imageManager = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface');
        Phake::when($this->imageManager)->generateAlternative(Phake::anyParameters())
            ->thenReturn($this->generatedFilePath);
        Phake::when($this->imageManager)->cropAndResize(Phake::anyParameters())->thenReturn($this->generatedFilePath);

        $this->thumbnailFormat = array(
            'max_width' => 117,
            'max_height' => 117,
            'compression_quality' => 75
        );

        $this->alternativesFormats = array(
            'format1' => array('max_height' => 100, 'compression_quality' => 75),
            'format2' => array('max_width' => 100, 'compression_quality' => 75)
        );

        $medias = array($this->fullMedia, $this->emptyMedia, $this->thumbnailNullMedia);

        foreach ($medias as $media) {
            Phake::when($media)->getAlternative('format1')->thenReturn('format1-' . $media->getFilesystemName());
            Phake::when($media)->getAlternative('format2')->thenReturn('format2-' . $media->getFilesystemName());
            Phake::when($media)->getAlternatives()->thenReturn(
                array(
                    'format1' => 'format1-' . $media->getFilesystemName(),
                    'format2' => 'format2-' . $media->getFilesystemName()
                )
            );
        }

        $this->strategy = new ImageStrategy(
            $this->fileSystem,
            $this->mediaStorageManager,
            $this->imageManager,
            $this->tmpDir,
            $this->thumbnailFormat,
            $this->alternativesFormats
        );
    }

    /**
     * Provide Media to check mime types
     */
    public function provideMimeTypes()
    {
        return array(
            array('imageMedia', true),
            array('videoMedia', false),
            array('audioMedia', false),
            array('pdfMedia', false),
            array('textMedia', false)
        );
    }

    /**
     * test generateThumbnail
     * 
     * @param string $mediaName
     * 
     * @dataProvider provideMedia
     */
    public function testGenerateThumbnail($mediaName)
    {
        $media = $this->{$mediaName};

        $this->strategy->generateThumbnail($media);

        Phake::verify($media)->setThumbnail(Phake::anyParameters());
        $this->assertAlternativeGenerated();
    }

    /**
     * test generateAlternatives
     * 
     * @param string $mediaNames
     * 
     * @dataProvider provideMedia
     */
    public function testGenerateAlternatives($mediaName)
    {
        $media = $this->{$mediaName};

        $this->strategy->generateAlternatives($media);

        $this->assertAlternativeGenerated(count($this->alternativesFormats));
        $this->assertOriginalRemoved($media);
    }

    /**
     * test deleteAlternatives
     * 
     * @param string $mediaName
     * 
     * @dataProvider provideMedia
     */
    public function testDeleteAlternatives($mediaName)
    {
        $media = $this->{$mediaName};

        $this->strategy->deleteAlternatives($media);

        $this->assertFileDeleted(
            'format1-' . $media->getFilesystemName(),
            $this->mediaStorageManager->exists('format1-' . $media->getFilesystemName())
        );
        $this->assertFileDeleted(
            'format2-' . $media->getFilesystemName(),
            $this->mediaStorageManager->exists('format2-' . $media->getFilesystemName())
        );
        $this->assertFileDeleted(
            $media->getFilesystemName(),
            $this->mediaStorageManager->exists($media->getFilesystemName())
        );
    }

    /**
     * test overrideAlternative
     * 
     * @param string $mediaName
     * @param string $formatName
     * 
     * @dataProvider provideMediaWithFormat
     */
    public function testOverrideAlternative($mediaName, $formatName)
    {
        $media = $this->{$mediaName};

        $this->strategy->overrideAlternative($media, 'somewhere', $formatName);

        $alternativeName = $media->getAlternative($formatName);

        $this->assertFileDeleted(
            $alternativeName,
            $this->mediaStorageManager->exists($alternativeName)
        );
        Phake::verify($this->mediaStorageManager)->uploadFile(Phake::anyParameters());
        Phake::verify($media)->addAlternative(Phake::anyParameters());
    }

    /**
     * Provide media with format
     */
    public function provideMediaWithFormat()
    {
        return array(
            array('fullMedia', 'format1'),
            array('fullMedia', 'format2'),
            array('emptyMedia', 'format1'),
            array('emptyMedia', 'format2'),
        );
    }

    /**
     * test cropAlternative
     * 
     * @dataProvider provideMediaWithFormat
     */
    public function testCropAlternative($mediaName, $formatName)
    {
        $media = $this->{$mediaName};
        $alternativeName = $media->getAlternative($formatName);
        $x = 50; $y = 60; $h = 70; $w = 80;

        $this->strategy->cropAlternative($media, $x, $y, $h, $w, $formatName);

        Phake::verify($this->mediaStorageManager)->downloadFile($media->getFilesystemName(), $this->tmpDir);
        Phake::verify($this->imageManager)
            ->cropAndResize($media->getFilesystemName(), $x, $y, $h, $w, $this->alternativesFormats[$formatName]);
        $this->assertFileDeleted(
            $alternativeName,
            $this->mediaStorageManager->exists($alternativeName)
        );
        Phake::verify($this->mediaStorageManager)->uploadFile(\Phake::anyParameters());
        Phake::verify($this->fileSystem)->remove(array($media->getFilesystemName()));
    }

    /**
     * Verify that $times alternatives are generated
     * 
     * @param int $times
     */
    protected function assertAlternativeGenerated($times = 1)
    {
        Phake::verify($this->imageManager, Phake::times($times))->generateAlternative(Phake::anyParameters());
        Phake::verify($this->mediaStorageManager, Phake::times($times))->uploadFile(Phake::anyParameters());
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('image_alternatives_strategy', $this->strategy->getName());
    }
}
