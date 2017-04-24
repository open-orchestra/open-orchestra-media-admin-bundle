<?php

namespace OpenOrchestra\MediaAdminBundle\Manager;

use OpenOrchestra\Media\Model\MediaFolderInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;

/**
 * Class FolderManager
 */
class FolderManager
{
    protected $documentManager;
    protected $mediaRepository;
    protected $folderRepository;

    /**
     * Constructor
     *
     * @param DocumentManager           $documentManager
     * @param MediaRepositoryInterface  $mediaRepository
     * @param FolderRepositoryInterface $folderRepository
     */
    public function __construct(
        DocumentManager $documentManager,
        MediaRepositoryInterface $mediaRepository,
        FolderRepositoryInterface $folderRepository
    ) {
        $this->documentManager = $documentManager;
        $this->mediaRepository = $mediaRepository;
        $this->folderRepository = $folderRepository;
    }

    /**
     * @param MediaFolderInterface $folder
     */
    public function deleteFolder($folder)
    {
        if ($this->isDeletable($folder)) {
            $this->documentManager->remove($folder);
        }
    }

    /**
     * @param MediaFolderInterface $folder
     *
     * @return bool
     */
    public function isDeletable(MediaFolderInterface $folder)
    {
        return $this->mediaRepository->countByFolderId($folder->getId()) == 0
            && $this->folderRepository->countChildren($folder->getId(), $folder->getSiteId()) == 0;
    }
}
