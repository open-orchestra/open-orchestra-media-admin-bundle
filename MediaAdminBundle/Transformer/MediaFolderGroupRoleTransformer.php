<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\HttpException\RoleNotFoundHttpException;
use OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\ApiBundle\Transformer\DocumentGroupRoleTransformer;
use OpenOrchestra\ApiBundle\Transformer\TransformerWithGroupInterface;
use OpenOrchestra\Backoffice\Collector\RoleCollectorInterface;
use OpenOrchestra\BackofficeBundle\Model\DocumentGroupRoleInterface;
use OpenOrchestra\BackofficeBundle\Model\GroupInterface;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;

/**
 * Class MediaFolderGroupRoleTransformer
 */
class MediaFolderGroupRoleTransformer extends DocumentGroupRoleTransformer
{
    protected $documentGroupRoleClass;
    protected $collector;
    protected $folderRepository;
    protected $currentSiteManager;

    /**
     * @param string                    $facadeClass
     * @param string                    $documentGroupRoleClass
     * @param RoleCollectorInterface    $collector
     * @param FolderRepositoryInterface $folderRepository
     */
    public function __construct(
        $facadeClass,
        $documentGroupRoleClass,
        RoleCollectorInterface $collector,
        FolderRepositoryInterface $folderRepository
    ) {
        parent::__construct($facadeClass, $documentGroupRoleClass, $collector);
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
        $parentAccess = $group->getDocumentRoleByTypeAndIdAndRole('folder', $folder->getParent()->getId(), $facade->name);
        if ($parentAccess instanceof DocumentGroupRoleInterface) {
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
