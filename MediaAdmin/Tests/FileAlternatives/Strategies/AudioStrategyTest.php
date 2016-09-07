<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\AudioStrategy;
use Phake;

/**
 * Class AudioStrategyTest
 */
class AudioStrategyTest extends AbstractFileAlternativesStrategy
{
    protected $thumbnail = 'audio.jpg';

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->strategy = new AudioStrategy(
            $this->fileSystem,
            $this->mediaStorageManager,
            $this->tmpDir,
            $this->thumbnail
        );
    }

    /**
     * Provide Media to check mime types
     */
    public function provideMimeTypes()
    {
        return array(
            array('imageMedia', false),
            array('videoMedia', false),
            array('audioMedia', true),
            array('pdfMedia', false),
            array('textMedia', false)
        );
    }

    /**
     * test getMediaType
     */
    public function testGetMediaType()
    {
        $this->assertSame('audio', $this->strategy->getMediaType());
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

        Phake::verify($media)->setThumbnail($this->thumbnail);
    }

    /**
     * test deleteThumbnail
     * 
     * @param string $mediaName
     * 
     * @dataProvider provideMedia
     */
    public function testDeleteThumbnail($mediaName)
    {
    }

    /**
     * @return array
     */
    public function provideFileMimeType()
    {
        return array(
            array('otherMimeType', false),
            array('audio/mp3', true)
        );
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('audio_alternatives_strategy', $this->strategy->getName());
    }
}
