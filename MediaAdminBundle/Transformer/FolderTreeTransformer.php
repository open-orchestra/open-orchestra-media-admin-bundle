<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;

/**
 * Class FolderTreeTransformer
 */
class FolderTreeTransformer extends AbstractTransformer
{
    /**
     * @param array $folderCollection
     *
     * @return FacadeInterface
     */
    public function transform($folderCollection)
    {
        $facade = $this->newFacade();


        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'folder_tree';
    }
}
