<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\PdfStrategy;
use Phake;

/**
 * Class PdfStrategyTest
 */
class PdfStrategyTest extends AbstractFileAlternativesStrategy
{
    protected $imageManager;
    protected $thumbnailFormat;

    protected $generatedFilePath = 'generated.jpg';

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->imageManager = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface');
        Phake::when($this->imageManager)->extractImageFromPdf(Phake::anyParameters())
            ->thenReturn($this->generatedFilePath);
        Phake::when($this->imageManager)->generateAlternative(Phake::anyParameters())
            ->thenReturn($this->generatedFilePath);

        $this->thumbnailFormat = array(
            'max_width' => 117,
            'max_height' => 117,
            'compression_quality' => 75
        );

        $this->strategy = new PdfStrategy(
            $this->fileSystem,
            $this->mediaStorageManager,
            $this->imageManager,
            $this->tmpDir,
            $this->thumbnailFormat
        );
    }

    /**
     * test getMediaType
     */
    public function testGetMediaType()
    {
        $this->assertSame('pdf', $this->strategy->getMediaType());
    }

    /**
     * test generateThumnail
     * 
     * @param string $mediaName
     * 
     * @dataProvider provideMedia
     */
    public function testGenerateThumbnail($mediaName)
    {
        $media = $this->{$mediaName};

        $this->strategy->generateThumbnail($media);

        Phake::verify($this->imageManager)
            ->extractImageFromPdf($this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName());

        Phake::verify($this->imageManager)
            ->generateAlternative($this->generatedFilePath, $this->thumbnailFormat);

        Phake::verify($this->mediaStorageManager)->uploadFile(Phake::anyParameters());

        Phake::verify($this->fileSystem)->remove(array($this->generatedFilePath));

        Phake::verify($media)->setThumbnail(Phake::anyParameters());
    }

    /**
     * Provide Media to check mime types
     */
    public function provideMimeTypes()
    {
        return array(
            array('imageMedia', false),
            array('videoMedia', false),
            array('audioMedia', false),
            array('pdfMedia', true),
            array('textMedia', false)
        );
    }

    /**
     * @return array
     */
    public function provideFileMimeType()
    {
        return array(
            array('otherMimeType', false),
            array('image/jpeg', false),
            array('image/png', false),
            array('application/pdf', true)
        );
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('pdf_alternatives_strategy', $this->strategy->getName());
    }
}
