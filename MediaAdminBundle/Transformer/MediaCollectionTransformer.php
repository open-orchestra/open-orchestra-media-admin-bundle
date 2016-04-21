<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class MediaCollectionTransformer
 */
class MediaCollectionTransformer extends AbstractSecurityCheckerAwareTransformer
{
    /** @var  FolderRepositoryInterface */
    protected $folderRepository;

    /**
     * @param string                        $facadeClass
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FolderRepositoryInterface     $folderRepository
     */
    public function __construct(
        $facadeClass,
        AuthorizationCheckerInterface $authorizationChecker,
        FolderRepositoryInterface $folderRepository
    ) {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->folderRepository = $folderRepository;
    }

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
        $facade = $this->newFacade();

        $facade->isFolderDeletable = $folderDeletable;
        $facade->parentId = $parentId;

        $folder = $this->folderRepository->find($folderId);

        foreach ($mixed as $media) {
            $facade->addMedia($this->getTransformer('media')->transform($media));
        }

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA, $folder)) {
            $facade->addLink('_self_add', $this->generateRoute('open_orchestra_api_media_upload', array(
                'folderId' => $folderId
            )));
        }

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER, $folder)) {
            $facade->addLink('_self_folder', $this->generateRoute('open_orchestra_media_admin_folder_form', array(
                'folderId' => $folderId
            )));
        }

        $facade->addLink('_media_types', $this->generateRoute('open_orchestra_api_media_type_list', array(
            'folderId' => $folderId
        )));

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA_FOLDER, $folder)) {
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
