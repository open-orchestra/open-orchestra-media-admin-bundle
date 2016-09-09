<?php

namespace OpenOrchestra\MediaAdminBundle\Manager;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class SaveMediaManagerInterface
 */
interface SaveMediaManagerInterface
{
    /**
     * @param UploadedFile $uploadedFile
     * 
     * @return string|null
     */
    public function getFilenameFromChunks(UploadedFile $uploadedFile);

    /**
     * Return true if the file is allowed to be uploaded based on its mime type
     * 
     * @param string $filename
     * 
     * @return bool
     *
     * @deprecated will be remove in 2.0
     */
    public function isFileAllowed($filename);

    /**
     * Create a media to fit an uploaded file
     *
     * @param UploadedFile $uploadedFile
     * @param string       $filename
     * @param string       $folderId
     *
     * @return MediaInterface
     * @deprecated will be remove in 2.0, use initializeMediaFromUploadedFile
     */
    public function createMediaFromUploadedFile(UploadedFile $uploadedFile, $filename, $folderId);

    /**
     * initialize a media to fit an uploaded file
     *
     * @param UploadedFile $uploadedFile
     * @param string       $folderId
     *
     * @return MediaInterface
     */
    public function initializeMediaFromUploadedFile(UploadedFile $uploadedFile, $folderId);

    /**
     * Save a media in database
     *
     * @param MediaInterface $media
     */
    public function saveMedia($media);
}
