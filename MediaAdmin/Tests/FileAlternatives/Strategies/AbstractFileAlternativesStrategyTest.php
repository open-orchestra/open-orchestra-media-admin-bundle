<?php

namespace OpenOrchestra\MediaAdmin\Tests\FileAlternatives\Strategy;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;

/**
 * Class AbstractFileAlternativesStrategy
 */
abstract class AbstractFileAlternativesStrategy extends AbstractBaseTestCase
{
    protected $strategy;
    protected $mediaStorageManager;
    protected $fileSystem;
    protected $tmpDir;

    protected $fullMedia;
    protected $fullMediaFileSystemName = 'original.jpg';
    protected $fullMediaThumbnailName = 'thumbnail.jpg';

    protected $emptyMedia;
    protected $emptyMediaFileSystemName = '';
    protected $emptyMediaThumbnailName = 'no-thumbnail.jpg';

    protected $thumbnailNullMedia;
    protected $thumbnailNullMediaThumbnailName = null;

    protected $imageMedia;
    protected $audioMedia;
    protected $videoMedia;
    protected $pdfMedia;
    protected $textMedia;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/../../Fixtures/Source';
        $this->fullMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->fullMedia)->getFilesystemName()->thenReturn($this->fullMediaFileSystemName);
        Phake::when($this->fullMedia)->getThumbnail()->thenReturn($this->fullMediaThumbnailName);

        $this->emptyMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->emptyMedia)->getFilesystemName()->thenReturn($this->emptyMediaFileSystemName);
        Phake::when($this->emptyMedia)->getThumbnail()->thenReturn($this->emptyMediaThumbnailName);

        $this->thumbnailNullMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->thumbnailNullMedia)->getThumbnail()->thenReturn($this->thumbnailNullMediaThumbnailName);

        $this->imageMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->imageMedia)->getFilesystemName()->thenReturn('fil-vertical.jpg');
        Phake::when($this->imageMedia)->getMimeType()->thenReturn('image/jpg');

        $this->audioMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->audioMedia)->getMimeType()->thenReturn('audio/mp3');

        $this->videoMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->videoMedia)->getFilesystemName()->thenReturn('video.mp4');
        Phake::when($this->videoMedia)->getMimeType()->thenReturn('video/mpeg');

        $this->pdfMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->pdfMedia)->getMimeType()->thenReturn('application/pdf');

        $this->textMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->textMedia)->getMimeType()->thenReturn('text/html');

        $this->fileSystem = Phake::mock('Symfony\Component\Filesystem\Filesystem');

        $this->mediaStorageManager = Phake::mock('OpenOrchestra\Media\Manager\MediaStorageManagerInterface');
        Phake::when($this->mediaStorageManager)->exists($this->fullMediaFileSystemName)->thenReturn(true);
        Phake::when($this->mediaStorageManager)->exists($this->fullMediaThumbnailName)->thenReturn(true);
        Phake::when($this->mediaStorageManager)->exists($this->emptyMediaFileSystemName)->thenReturn(false);
        Phake::when($this->mediaStorageManager)->exists($this->emptyMediaThumbnailName)->thenReturn(false);
        Phake::when($this->mediaStorageManager)->exists($this->thumbnailNullMediaThumbnailName)->thenReturn(true);
        Phake::when($this->mediaStorageManager)->downloadFile($this->fullMediaFileSystemName, $this->tmpDir)
            ->thenReturn($this->fullMediaFileSystemName);
        Phake::when($this->mediaStorageManager)->downloadFile($this->emptyMediaFileSystemName, $this->tmpDir)
            ->thenReturn($this->emptyMediaFileSystemName);
    }

    /**
     * test support
     * 
     * @param string $mediaType
     * @param bool   $expectedSupport
     * 
     * @dataProvider provideMimeTypes
     */
    public function testSupport($mediaType, $expectedSupport)
    {
        $this->assertSame($expectedSupport, $this->strategy->support($this->{$mediaType}));
    }

    /**
     * test getMediaType
     */
    abstract function testGetMediaType();

    /**
     * test getAlternatives
     * 
     * @param string $mediaName
     * 
     * @dataProvider provideMedia
     */
    public function testGenerateAlternatives($mediaName)
    {
        $media = $this->{$mediaName};

        $this->strategy->generateAlternatives($media);

        $this->assertOriginalRemoved($media);
    }

    /**
     * test setMediaInformation
     *
     * @param string $mediaName
     *
     * @dataProvider provideFileMediaInformation
     */
    public function testSetMediaInformation($mediaName)
    {
        $media = $this->{$mediaName};

        $this->strategy->setMediaInformation($media);

        Phake::verify($media, Phake::times(2))->addMediaInformation(Phake::anyParameters());
    }

    /**
     * Provide media
     */
    public function provideFileMediaInformation()
    {
        return array(
            array('imageMedia'),
            array('videoMedia'),
        );
    }

    /**
     * Assert that the original file is removed
     * 
     * @param MediaInterface $media
     */
    protected function assertOriginalRemoved($media)
    {
        if ($media->getFilesystemName() != '') {
            Phake::verify($this->fileSystem)->remove(
                array($this->tmpDir . DIRECTORY_SEPARATOR . $media->getFilesystemName())
            );
        } else {
            Phake::verify($this->fileSystem, Phake::never())->remove(Phake::anyParameters());
        }
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
        $media = $this->{$mediaName};

        $this->strategy->deleteThumbnail($media);

        $this->assertFileDeleted(
            $media->getThumbnail(),
            $this->mediaStorageManager->exists($media->getThumbnail())
        );
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
            $media->getFilesystemName(),
            $this->mediaStorageManager->exists($media->getFilesystemName())
        );
    }

    /**
     * Provide media
     */
    public function provideMedia()
    {
        return array(
            array('fullMedia'),
            array('emptyMedia'),
            array('thumbnailNullMedia')
        );
    }

    /**
     * Assert a file is deleted if existing
     * 
     *  @param string $fileName
     *  @param bool   $fileExists
     */
    protected function assertFileDeleted($fileName, $fileExists)
    {
        if (null !== $fileName && $fileExists) {
            Phake::verify($this->mediaStorageManager)->deleteContent($fileName);
        } else {
            Phake::verify($this->mediaStorageManager, Phake::never())->deleteContent($fileName);
        }
    }
}
