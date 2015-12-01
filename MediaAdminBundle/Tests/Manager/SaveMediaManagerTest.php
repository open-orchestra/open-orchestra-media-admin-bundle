<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Manager;

use Phake;
use OpenOrchestra\MediaAdminBundle\Manager\SaveMediaManager;

/**
 * Class SaveMediaManagerTest
 */
class SaveMediaManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveMediaManager
     */
    protected $mediaManager;

    protected $tmpDir;
    protected $uploadedMediaManager;
    protected $allowedMimeTypes = array('mimeType1', 'image/jpeg');
    protected $documentManager;
    protected $folderRepository;
    protected $mediaClass;
    protected $dispatcher;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->tmpDir = __DIR__.'/images';
        $this->uploadedMediaManager = Phake::mock('OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager');
        $this->documentManager = Phake::mock('Doctrine\ODM\MongoDB\DocumentManager');
        $this->folderRepository = Phake::mock('OpenOrchestra\Media\Repository\FolderRepositoryInterface');
        $this->dispatcher = Phake::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->mediaManager = new SaveMediaManager(
            $this->tmpDir,
            $this->uploadedMediaManager,
            $this->allowedMimeTypes,
            $this->documentManager,
            $this->folderRepository,
            $this->mediaClass,
            $this->dispatcher
        );
    }

    /**
     * @return array
     */
    public function provideMedias()
    {
        return array(
            array(
                array(
                    $this->createMockMedia('What-are-you-talking-about', 'jpg', 'mediaId1'),
                    $this->createMockMedia('rectangle-reference', 'jpg', 'mediaId2'),
                ),
            )
        );
    }

    /**
     * @param string $filename
     * @param bool   $expectedStatus
     *
     * @dataProvider provideFileType
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
     * @param mixed  $media
     * @param string $fileName
     */
    protected function assertUploadMedia($media, $fileName)
    {
        $file = $media->getFile();
        $tmpFilePath = $this->tmpDir . '/' . $fileName;
        Phake::verify($this->uploadedMediaManager)->uploadContent($fileName, file_get_contents($tmpFilePath));
    }

    /**
     * @param mixed $media
     */
    protected function assertSaveMedia($media)
    {
        $fileName = $media->getFile()->getClientOriginalName();
        $fileExtension = $media->getFile()->guessClientExtension();

        $this->assertRegExp('/'.$fileName .'.'. $fileExtension.'/', $media->getFilesystemName());
        Phake::verify($media)->setName($fileName);
        Phake::verify($media)->setMimeType($fileExtension);
    }

    /**
     * @param string $fileName
     * @param string $fileExtension
     * @param string $mediaId
     *
     * @return mixed
     */
    protected function createMockMedia($fileName, $fileExtension, $mediaId)
    {
        $file = Phake::mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        Phake::when($file)->guessClientExtension()->thenReturn($fileExtension);
        Phake::when($file)->getClientMimeType()->thenReturn($fileExtension);
        Phake::when($file)->getClientOriginalName()->thenReturn($fileName);

        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getFile()->thenReturn($file);
        Phake::when($media)->getId()->thenReturn($mediaId);
        Phake::when($media)->getFilesystemName()->thenReturn($fileName . "." . $fileExtension);

        return $media;
    }
}
