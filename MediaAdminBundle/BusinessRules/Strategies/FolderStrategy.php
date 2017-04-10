<?php

namespace OpenOrchestra\MediaAdminBundle\BusinessRules\Strategies;

use OpenOrchestra\Backoffice\BusinessRules\Strategies\AbstractBusinessRulesStrategy;
use OpenOrchestra\Backoffice\BusinessRules\Strategies\BusinessActionInterface;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\MediaAdminBundle\Manager\FolderManager;

/**
 * class FolderStrategy
 */
class FolderStrategy extends AbstractBusinessRulesStrategy
{
    protected $folderManager;

    /**
     * @param FolderManager $folderManager
     */
    public function __construct(
        FolderManager $folderManager
    ) {
        $this->folderManager = $folderManager;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return MediaFolderInterface::ENTITY_TYPE;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return array(
            BusinessActionInterface::DELETE => 'canDelete',
        );
    }

    /**
     * @param MediaFolderInterface $folder
     * @param array                $parameters
     *
     * @return boolean
     */
    public function canDelete(MediaFolderInterface $folder, array $parameters)
    {
        return $this->folderManager->isDeletable($folder);
    }
}
