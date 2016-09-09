<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Manager;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\Manager\SaveMediaManager;

/**
 * Class SaveMediaManagerTest
 */
class SaveMediaManagerTest extends AbstractBaseTestCase
{
    /**
     * @var SaveMediaManager
     */
    protected $mediaManager;

    protected $tmpDir;
    protected $mediaStorageManager;
    protected $allowedMimeTypes = array('mimeType1', 'image/jpeg');
    protected $documentManager;
    protected $folderRepository;
    protected $mediaClass = 'OpenOrchestra\MediaModelBundle\Document\Media';
    protected $dispatcher;

    protected $folder;
    protected $folderId = 'folderId';
    protected $uploadedFile;
    protected $originalName = 'original name';
    protected $mimeType = 'some mime type';
    protected $fileName = 'fake file name';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/../Fixtures';
        $this->mediaStorageManager = Phake::mock('OpenOrchestra\MediaFileBundle\Manager\MediaStorageManager');
        $this->documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');

        $this->folder = Phake::mock('OpenOrchestra\Media\Model\MediaFolderInterface');
        Phake::when($this->folder)->getId()->thenReturn($this->folderId);

        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        Phake::when($this->folderRepository)->find($this->folderId)->thenReturn($this->folder);

        $this->dispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->uploadedFile = Phake::mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        Phake::when($this->uploadedFile)->getClientOriginalName()->thenReturn($this->originalName);
        Phake::when($this->uploadedFile)->getMimeType()->thenReturn($this->mimeType);
        Phake::when($this->uploadedFile)->getFilename()->thenReturn($this->fileName);

        $this->mediaManager = new SaveMediaManager(
            $this->tmpDir,
            $this->mediaStorageManager,
            $this->allowedMimeTypes,
            $this->documentManager,
            $this->folderRepository,
            $this->mediaClass,
            $this->dispatcher
        );
    }

    /**
     * @param string $filename
     * @param bool   $expectedStatus
     *
     * @dataProvider provideFileType
     *
     * @deprecated will be remove in 2.0
     */
    public function testIsFileAllowed($filename, $expectedStatus)
    {
        $this->assertSame($expectedStatus, $this->mediaManager->isFileAllowed($filename));
    }

    /**
     * @return array
     */
    public function provideFileType()
    {
        return array(
            array('What-are-you-talking-about.jpg', true),
            array('hecommon.mp3', false)
        );
    }

    /**
     * Test initializeMediaFromUploadedFile
     */
    public function testInitializeMediaFromUploadedFile()
    {
        $media = $this->mediaManager->initializeMediaFromUploadedFile($this->uploadedFile, $this->folderId);

        $this->assertSame($this->uploadedFile, $media->getFile());
        $this->assertSame($this->fileName, $media->getFilesystemName());
        $this->assertSame($this->folderId, $media->getMediaFolder()->getId());
        $this->assertSame($this->originalName, $media->getName());
        $this->assertSame($this->mimeType, $media->getMimeType());
    }

    /**
     * Test createMediaFromUploadedFile
     *
     * @deprecated will be remove in 2.0, use initializeMediaFromUploadedFile
     */
    public function testCreateMediaFromUploadedFile()
    {
        $filename = 'file.pdf';
        $media = $this->mediaManager->createMediaFromUploadedFile($this->uploadedFile, $filename, $this->folderId);
        $this->assertSame($this->uploadedFile, $media->getFile());
        $this->assertSame('file.pdf', $media->getFilesystemName());
        $this->assertSame($this->folderId, $media->getMediaFolder()->getId());
        Phake::verify($this->documentManager)->persist($media);
        Phake::verify($this->documentManager)->flush();
        $this->assertSame($this->originalName, $media->getName());
        $this->assertSame($this->mimeType, $media->getMimeType());
        Phake::verify($this->dispatcher)->dispatch(Phake::anyParameters());
    }

    /**
     * Test save media
     */
    public function testSaveMedia()
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getFile()->thenReturn($this->uploadedFile);

        $this->mediaManager->saveMedia($media);

        Phake::verify($this->mediaStorageManager)->uploadFile(Phake::anyParameters());

        Phake::verify($this->documentManager)->persist($media);
        Phake::verify($this->documentManager)->flush();
        Phake::verify($this->dispatcher)->dispatch(Phake::anyParameters());
    }
}
