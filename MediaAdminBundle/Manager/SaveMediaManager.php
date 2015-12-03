<?php

namespace OpenOrchestra\MediaAdminBundle\Manager;

use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Flow\Basic as FlowBasic;

/**
 * Class SaveMediaManager
 */
class SaveMediaManager implements SaveMediaManagerInterface
{
    protected $tmpDir;
    protected $uploadedMediaManager;
    protected $allowedMimeTypes;
    protected $objectManager;
    protected $folderRepository;
    protected $mediaClass;
    protected $dispatcher;

    /**
     * @param string                    $tmpDir
     * @param UploadedMediaManager      $uploadedMediaManager
     * @param array                     $allowedMimeTypes
     * @param objectManager             $objectManager
     * @param FolderRepositoryInterface $folderRepository
     * @param string                    $mediaClass
     * @param EventDispatcherInterface  $dispatcher
     */
    public function __construct(
        $tmpDir,
        UploadedMediaManager $uploadedMediaManager,
        array $allowedMimeTypes,
        ObjectManager $objectManager,
        FolderRepositoryInterface $folderRepository,
        $mediaClass,
        EventDispatcherInterface $dispatcher
    ) {
        $this->tmpDir = $tmpDir;
        $this->uploadedMediaManager = $uploadedMediaManager;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->objectManager = $objectManager;
        $this->folderRepository = $folderRepository;
        $this->mediaClass = $mediaClass;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Check if all chunks of a file being uploaded have been received
     * If yes, return the name of the reassembled temporary file
     * 
     * @param UploadedFile $uploadedFile
     * 
     * @return string|null
     */
    public function getFilenameFromChunks(UploadedFile $uploadedFile)
    {
        $filename = time() . '-' . $uploadedFile->getClientOriginalName();

        if (FlowBasic::save($this->tmpDir . DIRECTORY_SEPARATOR . $filename, $this->tmpDir)) {

            return $filename;
        }

        return null;
    }

    /**
     * Return true if the file is allowed to be uploaded based on its mime type
     * 
     * @param $filename
     * 
     * @return bool
     */
    public function isFileAllowed($filename)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileMimeType = finfo_file($finfo, $this->tmpDir . '/' . $filename);

        return in_array($fileMimeType, $this->allowedMimeTypes);
    }

    /**
     * Create a media to fit an uploaded file
     * 
     * @param UploadedFile $uploadedFile
     * @param string       $filename
     * @param string       $folderId
     * 
     * @return MediaInterface
     */
    public function createMediaFromUploadedFile(UploadedFile $uploadedFile, $filename, $folderId)
    {
        $media = new $this->mediaClass();
        $media->setFile($uploadedFile);
        $media->setFilesystemName($filename);
        $media->setMediaFolder($this->folderRepository->find($folderId));

        if (null !== $uploadedFile) {
            $media = $this->processMedia($media, $uploadedFile, $filename);
        }

        $this->objectManager->persist($media);
        $this->objectManager->flush();

        return $media;
    }

    /**
     * Process $uploadedFile (thumbnail + storage) and attach it to $media
     *
     * @param MediaInterface $media
     * @param UploadedFile   $uploadedFile
     * @param string         $filename
     *
     * @return MediaInterface
     */
    protected function processMedia($media, $uploadedFile, $filename)
    {
        $media->setName($uploadedFile->getClientOriginalName());
        $media->setMimeType($uploadedFile->getClientMimeType());

        $this->uploadedMediaManager->uploadContent(
            $filename,
            file_get_contents($this->tmpDir . '/' . $filename)
        );

        $event = new MediaEvent($media);
        $this->dispatcher->dispatch(MediaEvents::ADD_MEDIA, $event);

        return $media;
    }
}

