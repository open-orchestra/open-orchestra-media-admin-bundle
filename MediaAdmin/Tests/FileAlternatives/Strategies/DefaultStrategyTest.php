<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileAlternatives\Strategy;

use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\DefaultStrategy;
use Phake;

/**
 * Class DefaultStrategyTest
 */
class DefaultStrategyTest extends AbstractFileAlternativesStrategy
{
    protected $thumbnail = 'default.jpg';

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->strategy = new DefaultStrategy(
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
            array('imageMedia', true),
            array('videoMedia', true),
            array('audioMedia', true),
            array('pdfMedia', true),
            array('textMedia', true)
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
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('default_alternatives_strategy', $this->strategy->getName());
    }
}
