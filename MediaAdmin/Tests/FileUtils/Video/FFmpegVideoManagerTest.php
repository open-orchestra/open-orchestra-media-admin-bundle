<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileUtils\Video;

use Phake;
use OpenOrchestra\MediaAdmin\Tests\FileUtils\AbstractFileUtilsManager;
use FFMpeg\FFMpeg;
use OpenOrchestra\MediaAdmin\FileUtils\Video\FFmpegVideoManager;

/**
 * Class FFmpegVideoManagerTest
 */
class FFmpegVideoManagerTest extends AbstractFileUtilsManager
{
    /**
     * @var FFmpegVideoManager
     */
    protected $manager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->manager = new FFmpegVideoManager(FFMpeg::create());
    }

    /**
     * test extractImageFromVideo
     */
    public function testExtractImageFromVideo()
    {
        $generatedFile = $this->manager->extractImageFromVideo($this->fixturesPath . 'Source/video.mp4');

        $this->assertFileCorrectlyGenerated($this->fixturesPath . 'Reference/video.jpg', $generatedFile);
    }
}
