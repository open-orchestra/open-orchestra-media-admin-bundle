<?php

namespace OpenOrchestra\MediaAdminBundle\Manager;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Media\Thumbnail\ThumbnailManager;
use OpenOrchestra\MediaFileBundle\Manager\UploadedMediaManager;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Flow\Basic as FlowBasic;

/**
 * Class SaveMediaManager
 */
class SaveMediaManager implements SaveMediaManagerInterface
{
    protected $tmpDir;
    protected $thumbnailManager;
    protected $uploadedMediaManager;
    protected $allowedMimeTypes;
    protected $documentManager;
    protected $folderRepository;
    protected $mediaClass;

    /**
     * @param string                    $tmpDir
     * @param ThumbnailManager          $thumbnailManager
     * @param UploadedMediaManager      $uploadedMediaManager
     * @param array                     $allowedMimeTypes
     * @param DocumentManager           $documentManager
     * @param FolderRepositoryInterface $folderRepository
     * @param string                    $mediaClass
     */
    public function __construct(
        $tmpDir,
        ThumbnailManager $thumbnailManager,
        UploadedMediaManager $uploadedMediaManager,
        array $allowedMimeTypes,
        DocumentManager $documentManager,
        FolderRepositoryInterface $folderRepository,
        $mediaClass
    ) {
        $this->tmpDir = $tmpDir;
        $this->thumbnailManager = $thumbnailManager;
        $this->uploadedMediaManager = $uploadedMediaManager;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->documentManager = $documentManager;
        $this->folderRepository = $folderRepository;
        $this->mediaClass = $mediaClass;
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
        $filename = sha1(uniqid(mt_rand(), true))
            . pathinfo(
                $this->tmpDir . '/' . $uploadedFile->getClientOriginalName(), PATHINFO_FILENAME
            ) . '.' . $uploadedFile->guessClientExtension();

        if (FlowBasic::save($this->tmpDir . '/' . $filename, $this->tmpDir)) {
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
            $media->setName($uploadedFile->getClientOriginalName());
            $media->setMimeType($uploadedFile->getClientMimeType());
            $this->thumbnailManager->generateThumbnailName($media);
        }

        $this->documentManager->persist($media);
        $this->documentManager->flush();

        if (null !== $uploadedFile) {
            $tmpFilePath = $this->tmpDir . '/' . $filename;
            $this->uploadedMediaManager->uploadContent($filename, file_get_contents($tmpFilePath));
            $this->thumbnailManager->generateThumbnail($media);
        }

        return $media;
    }
}

