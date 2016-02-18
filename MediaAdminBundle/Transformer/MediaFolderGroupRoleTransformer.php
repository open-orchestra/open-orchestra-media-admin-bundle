<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\ApiBundle\Transformer\ModelGroupRoleTransformer;
use OpenOrchestra\Backoffice\Collector\RoleCollectorInterface;
use OpenOrchestra\Backoffice\Model\ModelGroupRoleInterface;
use OpenOrchestra\Backoffice\Model\GroupInterface;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;

/**
 * Class MediaFolderGroupRoleTransformer
 */
class MediaFolderGroupRoleTransformer extends ModelGroupRoleTransformer
{
    protected $modelGroupRoleClass;
    protected $collector;
    protected $folderRepository;
    protected $currentSiteManager;

    /**
     * @param string                    $facadeClass
     * @param string                    $modelGroupRoleClass
     * @param RoleCollectorInterface    $collector
     * @param FolderRepositoryInterface $folderRepository
     */
    public function __construct(
        $facadeClass,
        $modelGroupRoleClass,
        RoleCollectorInterface $collector,
        FolderRepositoryInterface $folderRepository
    ) {
        parent::__construct($facadeClass, $modelGroupRoleClass, $collector);
        $this->folderRepository = $folderRepository;
    }

    /**
     * @param GroupInterface  $group
     * @param FacadeInterface $facade
     *
     * @return bool
     */
    protected function isParentGranted(GroupInterface $group, FacadeInterface $facade)
    {
        $folder = $this->folderRepository->find($facade->document);
        $parentAccess = $group->getModelRoleByTypeAndIdAndRole(FolderInterface::GROUP_ROLE_TYPE, $folder->getParent()->getId(), $facade->name);
        if ($parentAccess instanceof ModelGroupRoleInterface) {
            return $parentAccess->isGranted();
        }

        return false;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_folder_group_role';
    }
}
