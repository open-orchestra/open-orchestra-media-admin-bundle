<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\Media\Model\FolderInterface;

/**
 * Class FolderTransformer
 */
class FolderTransformer extends AbstractTransformer
{
    /**
     * @param FolderInterface $folder
     *
     * @return FacadeInterface
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

        foreach($folder->getSites() as $site) {
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
