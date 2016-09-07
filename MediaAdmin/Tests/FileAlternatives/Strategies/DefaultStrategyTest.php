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
    protected $allowedMineType = array('fakeMineType');

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
            $this->thumbnail,
            $this->allowedMineType
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
     * test getMediaType
     */
    public function testGetMediaType()
    {
        $this->assertSame('default', $this->strategy->getMediaType());
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
            array('fakeMineType', true)
        );
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('default_alternatives_strategy', $this->strategy->getName());
    }
}
