<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\Media\Model\FolderInterface;

/**
 * Class FolderTreeTransformer
 */
class FolderTreeTransformer extends AbstractTransformer
{
    /**
     * @param $folderCollection
     *
     * @return FacadeInterface
     */
    public function transform($folderCollection)
    {
        if (empty($folderCollection)) {
            return array();
        }

        $facade = $this->newFacade();

        if ($folderCollection instanceof FolderInterface) {
            $facade->folder = $this->getTransformer('folder')->transform($folderCollection);
            foreach ($folderCollection->getSubFolders() as $subFolders) {
                $facade->addChild($this->getTransformer('folder_tree')->transform($subFolders));
            }
        } else {
            $facade->folder = null;
            foreach ($folderCollection as $folder) {
                $facade->addChild($this->getTransformer('folder_tree')->transform($folder));
            }
        }

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
