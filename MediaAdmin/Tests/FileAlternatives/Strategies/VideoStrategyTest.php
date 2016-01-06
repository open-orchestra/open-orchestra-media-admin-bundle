<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\VideoStrategy;
use Phake;

/**
 * Class VideoStrategyTest
 */
class VideoStrategyTest extends AbstractFileAlternativesStrategy
{
    protected $imageManager;
    protected $videoManager;
    protected $thumbnailFormat;

    protected $generatedFilePath = 'generated.jpg';

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->imageManager = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Image\ImageManagerInterface');

        $this->videoManager = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Video\VideoManagerInterface');
        Phake::when($this->videoManager)->extractImageFromVideo(Phake::anyParameters())
            ->thenReturn($this->generatedFilePath);

        $this->thumbnailFormat = array(
            'max_width' => 117,
            'max_height' => 117,
            'compression_quality' => 75
        );

        $this->strategy = new VideoStrategy(
            $this->fileSystem,
            $this->mediaStorageManager,
            $this->videoManager,
            $this->imageManager,
            $this->tmpDir,
            $this->thumbnailFormat
        );
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

        Phake::verify($this->videoManager)
            ->extractImageFromVideo($this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName(), 1);

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
            array('videoMedia', true),
            array('audioMedia', false),
            array('pdfMedia', false),
            array('textMedia', false)
        );
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('video_alternatives_strategy', $this->strategy->getName());
    }
}
