<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Thumbnail\Strategies;

use OpenOrchestra\MediaAdmin\Thumbnail\Strategies\VideoToImageManager;

use Phake;

/**
 * Class VideoToImageManagerTest
 */
class VideoToImageManagerTest extends AbstractStrategyTest
{
    protected $video;
    protected $videoManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $this->videoManager = Phake::mock('OpenOrchestra\MediaAdmin\FileUtils\Video\VideoManagerInterface');
        $this->manager = new VideoToImageManager($this->tmpDir, $this->tmpDir, $this->videoManager);
    }

    /**
     * @return array
     */
    public function provideMimeType()
    {
        return array(
            array('application/x-authorware-map', false),
            array('text/plain', false),
            array('audio/it', false),
            array('music/crescendo', false),
            array('image/naplps', false),
            array('video/vnd.vivo', true),
            array('video/x-fli', true),
        );
    }

    /**
     * @return array
     */
    public function provideNameAndExtension()
    {
        return array(
            array('test', 'avi'),
            array('autre', 'mp4'),
            array('video', 'wmv'),
        );
    }

    /**
     * @param string $fileName
     * @param string $fileExtension
     *
     * @dataProvider provideFileNameAndExtension
     */
    public function testGenerateThumbnail($fileName, $fileExtension)
    {
        Phake::when($this->media)->getFilesystemName()->thenReturn($fileName. '.' . $fileExtension);
        Phake::when($this->media)->getThumbnail()->thenReturn($fileName. '.jpg');

        $this->manager->generateThumbnail($this->media);

        $path = $this->tmpDir .'/'. $fileName . '.' . $fileExtension;
        $pathFrame = $this->tmpDir .'/'. $fileName. '.jpg';
        Phake::verify($this->videoManager)->createFrame($path, $pathFrame, 1);
    }

    /**
     * @return array
     */
    public function provideFileNameAndExtension()
    {
        return array(
            array('video', '3gp'),
            array('video', 'mp4'),
        );
    }

    /**
     * Test name
     */
    public function testGetName()
    {
        $this->assertSame('video_to_image', $this->manager->getName());
    }
}
