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
     * Check if all chunks of a file being uploaded have been received
     * If yes, return the name of the reassembled temporary file
     *
     * @param UploadedFile $uploadedFile
     *
     * @return UploadedFile|null
     */
    public function getFileFromChunks(UploadedFile $uploadedFile);

    /**
     * initialize a media to fit an uploaded file
     *
     * @param UploadedFile $uploadedFile
     * @param string       $folderId
     * @param string       $siteId
     *
     * @return MediaInterface
     */
    public function initializeMediaFromUploadedFile(UploadedFile $uploadedFile, $folderId, $siteId);

    /**
     * Save a media in database
     *
     * @param MediaInterface $media
     */
    public function saveMedia($media);
}
