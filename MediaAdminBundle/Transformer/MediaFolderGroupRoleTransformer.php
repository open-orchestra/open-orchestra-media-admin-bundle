<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\ApiBundle\Exceptions\HttpException\RoleNotFoundHttpException;
use OpenOrchestra\ApiBundle\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\ApiBundle\Transformer\TransformerWithGroupInterface;
use OpenOrchestra\Backoffice\Collector\RoleCollectorInterface;
use OpenOrchestra\BackofficeBundle\Model\GroupInterface;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;

/**
 * Class MediaFolderGroupRoleTransformer
 */
class MediaFolderGroupRoleTransformer extends AbstractTransformer implements TransformerWithGroupInterface
{
    protected $mediaFolderRoleGroupClass;
    protected $collector;
    protected $folderRepository;
    protected $currentSiteManager;

    /**
     * @param string                    $facadeClass
     * @param string                    $mediaFolderRoleGroupClass
     * @param RoleCollectorInterface    $collector
     * @param FolderRepositoryInterface $folderRepository
     */
    public function __construct(
        $facadeClass,
        $mediaFolderRoleGroupClass,
        RoleCollectorInterface $collector,
        FolderRepositoryInterface $folderRepository
    ) {
        parent::__construct($facadeClass);
        $this->mediaFolderRoleGroupClass = $mediaFolderRoleGroupClass;
        $this->collector = $collector;
        $this->folderRepository = $folderRepository;
    }

    /**
     * @param MediaFolderGroupRoleInterface $mediaFolderGroupRole
     *
     * @return FacadeInterface
     *
     * @throws TransformerParameterTypeException
     */
    public function transform($mediaFolderGroupRole)
    {
        if (!$mediaFolderGroupRole instanceof MediaFolderGroupRoleInterface) {
            throw new TransformerParameterTypeException();
        }

        $facade = $this->newFacade();

        $facade->folder = $mediaFolderGroupRole->getFolderId();
        $facade->name = $mediaFolderGroupRole->getRole();
        $facade->accessType = $mediaFolderGroupRole->getAccessType();

        return $facade;
    }

    /**
     * @param GroupInterface                     $group
     * @param FacadeInterface                    $mediaFolderRoleFacade
     * @param MediaFolderGroupRoleInterface|null $source
     *
     * @throws RoleNotFoundHttpException
     * @throws TransformerParameterTypeException
     *
     * @return null|MediaFolderGroupRoleInterface
     */
    public function reverseTransformWithGroup(GroupInterface $group, FacadeInterface $mediaFolderRoleFacade, $source = null)
    {
        if (!$source instanceof MediaFolderGroupRoleInterface) {
            $source = new $this->mediaFolderRoleGroupClass();
        }

        if (!$this->collector->hasRole($mediaFolderRoleFacade->name)) {
            throw new RoleNotFoundHttpException();
        }

        $source->setFolderId($mediaFolderRoleFacade->folder);
        $source->setRole($mediaFolderRoleFacade->name);
        $source->setAccessType($mediaFolderRoleFacade->accessType);

        if (MediaFolderGroupRoleInterface::ACCESS_INHERIT === $mediaFolderRoleFacade->accessType) {
            $folder = $this->folderRepository->find($mediaFolderRoleFacade->folder);
            $parentAccess = $group->getMediaFolderRoleByMediaFolderAndRole($folder->getParent()->getId(), $mediaFolderRoleFacade->name);
            if ($parentAccess instanceof MediaFolderGroupRoleInterface) {
                $source->setGranted($parentAccess->isGranted());
            }
        } else {
            $isGranted = (MediaFolderGroupRoleInterface::ACCESS_GRANTED === $mediaFolderRoleFacade->accessType) ? true : false;
            $source->setGranted($isGranted);
        }

        return $source;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_folder_group_role';
    }
}
