<?php

namespace OpenOrchestra\MediaAdminBundle\Manager;

use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Media\Manager\MediaStorageManagerInterface;
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
    protected $mediaStorageManager;
    protected $allowedMimeTypes;
    protected $objectManager;
    protected $folderRepository;
    protected $mediaClass;
    protected $dispatcher;
    protected $frontLanguages;

    /**
     * @param string                       $tmpDir
     * @param MediaStorageManagerInterface $mediaStorageManager
     * @param array                        $allowedMimeTypes
     * @param objectManager                $objectManager
     * @param FolderRepositoryInterface    $folderRepository
     * @param string                       $mediaClass
     * @param EventDispatcherInterface     $dispatcher
     * @param array                        $frontLanguages
     */
    public function __construct(
        $tmpDir,
        MediaStorageManagerInterface $mediaStorageManager,
        array $allowedMimeTypes,
        ObjectManager $objectManager,
        FolderRepositoryInterface $folderRepository,
        $mediaClass,
        EventDispatcherInterface $dispatcher,
        array $frontLanguages
    ) {
        $this->tmpDir = $tmpDir;
        $this->mediaStorageManager = $mediaStorageManager;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->objectManager = $objectManager;
        $this->folderRepository = $folderRepository;
        $this->mediaClass = $mediaClass;
        $this->dispatcher = $dispatcher;
        $this->frontLanguages = array_keys($frontLanguages);
    }

    /**
     * Check if all chunks of a file being uploaded have been received
     * If yes, return the name of the reassembled temporary file
     *
     * @param UploadedFile $uploadedFile
     *
     * @return UploadedFile|null
     */
    public function getFileFromChunks(UploadedFile $uploadedFile)
    {
        $filename = time() . '-' . $uploadedFile->getClientOriginalName();
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . $filename;

        if (FlowBasic::save($path, $this->tmpDir)) {
            return new UploadedFile($path, $uploadedFile->getClientOriginalName(), $uploadedFile->getClientMimeType());
        }

        return null;
    }

    /**
     * initialize a media to fit an uploaded file
     * 
     * @param UploadedFile $uploadedFile
     * @param string       $folderId
     * @param string       $siteId
     * @param string|null  $title
     *
     * @return MediaInterface
     */
    public function initializeMediaFromUploadedFile(UploadedFile $uploadedFile, $folderId, $siteId, $title = null)
    {
        /** @var MediaInterface $media */
        $media = new $this->mediaClass();
        $media->setSiteId($siteId);
        $media->setFile($uploadedFile);
        $media->setFilesystemName($uploadedFile->getFilename());
        $media->setMediaFolder($this->folderRepository->find($folderId));
        $media->setName($uploadedFile->getClientOriginalName());
        $media->setMimeType($uploadedFile->getMimeType());
        if (null === $title) {
            $title = $uploadedFile->getFilename();
        }
        foreach ($this->frontLanguages as $language) {
            $media->addTitle($language, $title);
        }

        return $media;
    }

    /**
     * Save a media in database
     *
     * @param MediaInterface $media
     */
    public function saveMedia($media)
    {
        $file = $media->getFile();
        $this->mediaStorageManager->uploadFile($file->getFilename(), $file->getRealPath(), false);

        $this->objectManager->persist($media);
        $this->objectManager->flush();

        $event = new MediaEvent($media);
        $this->dispatcher->dispatch(MediaEvents::MEDIA_ADD, $event);
    }
}

