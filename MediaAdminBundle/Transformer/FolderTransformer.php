<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\MediaAdminBundle\Facade\FolderFacade;

/**
 * Class FolderTransformer
 */
class FolderTransformer extends AbstractTransformer
{
    /**
     * @param FolderInterface $folder
     *
     * @return FolderFacade
     *
     * @throws TransformerParameterTypeException
     */
    public function transform($folder)
    {
        if (!$folder instanceof FolderInterface) {
            throw new TransformerParameterTypeException();
        }
        $facade = $this->newFacade();
        $facade->folderId = $folder->getId();
        $facade->name = $folder->getName();
        $facade->createdAt = $folder->getCreatedAt();
        $facade->updatedAt = $folder->getUpdatedAt();
        if ($folder->getParent() instanceof FolderInterface) {
            $facade->parentId = $folder->getParent()->getId();
        } else {
            $facade->parentId = FolderInterface::ROOT_PARENT_ID;
        }
        foreach ($folder->getSites() as $site) {
            $facade->addSite($site);
        }

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'folder';
    }
}
