<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\MediaAdminBundle\Facade\MediaCollectionFacade;

/**
 * Class MediaCollectionTransformer
 */
class MediaCollectionTransformer extends AbstractSecurityCheckerAwareTransformer
{
    /**
     * @param ArrayCollection $mixed
     * @param string|null     $folderId
     * @param bool            $folderDeletable
     * @param string|null     $parentId
     *
     * @return FacadeInterface
     */
    public function transform($mixed, $folderId = null, $folderDeletable = false, $parentId = null)
    {
        $facade = new MediaCollectionFacade();

        $facade->isFolderDeletable = $folderDeletable;
        $facade->parentId = $parentId;

        foreach ($mixed as $media) {
            $facade->addMedia($this->getTransformer('media')->transform($media));
        }

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA)) {
            $facade->addLink('_self_add', $this->generateRoute('open_orchestra_api_media_upload', array(
                'folderId' => $folderId
            )));
        }

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER)) {
            $facade->addLink('_self_folder', $this->generateRoute('open_orchestra_media_admin_folder_form', array(
                'folderId' => $folderId
            )));
        }

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA_FOLDER)) {
            $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_folder_delete', array(
                'folderId' => $folderId
            )));
        }

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_collection';
    }
}
