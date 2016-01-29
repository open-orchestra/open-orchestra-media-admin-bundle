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

/**
 * Class MediaFolderGroupRoleTransformer
 */
class MediaFolderGroupRoleTransformer extends AbstractTransformer implements TransformerWithGroupInterface
{
    protected $mediaFolderRoleGroupClass;
    protected $collector;
    protected $nodeRepository;
    protected $currentSiteManager;

    /**
     * @param string                  $facadeClass
     * @param string                  $mediaFolderRoleGroupClass
     * @param RoleCollectorInterface  $collector
     * @param CurrentSiteIdInterface  $currentSiteManager
     */
    public function __construct(
        $facadeClass,
        $mediaFolderRoleGroupClass,
        RoleCollectorInterface $collector,
        CurrentSiteIdInterface $currentSiteManager
    ) {
        parent::__construct($facadeClass);
        $this->mediaFolderRoleGroupClass = $mediaFolderRoleGroupClass;
        $this->collector = $collector;
        $this->currentSiteManager = $currentSiteManager;
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
            //todo $parent->isGranted()?
            $source->setGranted(true);
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
