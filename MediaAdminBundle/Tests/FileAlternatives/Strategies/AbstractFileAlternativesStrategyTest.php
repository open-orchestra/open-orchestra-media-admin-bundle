<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\FileAlternatives\Strategy;

use Phake;

/**
 * Class AbstractFileAlternativesStrategy
 */
abstract class AbstractFileAlternativesStrategy extends \PHPUnit_Framework_TestCase
{
    protected $strategy;
    protected $mediaStorageManager;
    protected $fileSystem;
    protected $tmpDir = '/tmp';

    protected $fullMedia;
    protected $fullMediaFileSystemName = 'original.jpg';
    protected $fullMediaThumbnailName = 'thumbnail.jpg';

    protected $emptyMedia;
    protected $emptyMediaFileSystemName = '';
    protected $emptyMediaThumbnailName = 'no-thumbnail.jpg';

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
        $this->fullMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->fullMedia)->getFilesystemName()->thenReturn($this->fullMediaFileSystemName);
        Phake::when($this->fullMedia)->getThumbnail()->thenReturn($this->fullMediaThumbnailName);

        $this->emptyMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->emptyMedia)->getFilesystemName()->thenReturn($this->emptyMediaFileSystemName);
        Phake::when($this->emptyMedia)->getThumbnail()->thenReturn($this->emptyMediaThumbnailName);

        $this->imageMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->imageMedia)->getMimeType()->thenReturn('image/jpg');

        $this->audioMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->audioMedia)->getMimeType()->thenReturn('audio/mp3');

        $this->videoMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->videoMedia)->getMimeType()->thenReturn('video/mpeg');

        $this->pdfMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->pdfMedia)->getMimeType()->thenReturn('application/pdf');

        $this->textMedia = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->textMedia)->getMimeType()->thenReturn('text/html');

        $this->fileSystem = Phake::mock('Symfony\Component\Filesystem\Filesystem');

        $this->mediaStorageManager = Phake::mock('OpenOrchestra\MediaFileBundle\Manager\MediaStorageManager');
        Phake::when($this->mediaStorageManager)->exits($this->fullMediaFileSystemName)->thenReturn(true);
        Phake::when($this->mediaStorageManager)->exits($this->fullMediaThumbnailName)->thenReturn(true);
        Phake::when($this->mediaStorageManager)->exits($this->emptyMediaFileSystemName)->thenReturn(false);
        Phake::when($this->mediaStorageManager)->exits($this->emptyMediaThumbnailName)->thenReturn(false);
        Phake::when($this->mediaStorageManager)->downloadFile($this->fullMediaFileSystemName, $this->tmpDir)
            ->thenReturn($this->fullMediaFileSystemName);
        Phake::when($this->mediaStorageManager)->downloadFile($this->emptyMediaFileSystemName, $this->tmpDir)
            ->thenReturn($this->emptyMediaFileSystemName);
    }

    /**
     * test support
     * 
     * @param string $media
     * @param bool   $expectedSupport
     * 
     * @dataProvider provideMimeTypes
     */
    public function testSupport($mediaType, $expectedSupport)
    {
        $this->assertSame($expectedSupport, $this->strategy->support($this->{$mediaType}));
    }

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
     * test getAlternatives
     * 
     * @param string $mediaName
     * @param array  $expectedAlternatives
     * 
     * @dataProvider provideAlternatives
     */
    public function testGetAlternatives($mediaName, array $expectedAlternatives)
    {
        $media = $this->{$mediaName};

        $alternatives = $this->strategy->getAlternatives($media);

        $this->assertSame($expectedAlternatives, $alternatives);
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
            array('emptyMedia')
        );
    }

    /**
     * Provide media
     */
    public function provideAlternatives()
    {
        return array(
            array('fullMedia', array()),
            array('emptyMedia', array())
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
        if ($fileExists) {
            Phake::verify($this->mediaStorageManager)->deleteContent($fileName);
        } else {
            Phake::verify($this->mediaStorageManager, Phake::never())->deleteContent($fileName);
        }
    }
}
