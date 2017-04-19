<?php

namespace OpenOrchestra\MediaAdminBundle\Manager;

use OpenOrchestra\Media\Model\MediaFolderInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;

/**
 * Class FolderManager
 */
class FolderManager
{
    protected $documentManager;
    protected $mediaRepository;

    /**
     * Constructor
     *
     * @param DocumentManager          $documentManager
     * @param MediaRepositoryInterface $mediaRepository
     */
    public function __construct(DocumentManager $documentManager, MediaRepositoryInterface $mediaRepository)
    {
        $this->documentManager = $documentManager;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @param MediaFolderInterface $folder
     */
    public function deleteTree($folder)
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
        return $this->countMediaTree($folder) == 0;
    }

    /**
     * @param MediaFolderInterface $folder
     *
     * @return int
     */
    protected function countMediaTree(MediaFolderInterface $folder)
    {
        $count = $this->mediaRepository->countByFolderId($folder->getId());
        $subFolders = $folder->getSubFolders();
        foreach ($subFolders as $subFolder) {
            $count += $this->countMediaTree($subFolder, $count);
        }

        return $count;
    }
}
