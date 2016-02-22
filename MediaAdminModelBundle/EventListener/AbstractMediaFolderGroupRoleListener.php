<?php

namespace OpenOrchestra\MediaAdminModelBundle\EventListener;

use OpenOrchestra\Backoffice\Model\ModelGroupRoleInterface;
use OpenOrchestra\Backoffice\Model\GroupInterface;
use OpenOrchestra\Media\Model\FolderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class AbstractMediaFolderGroupRoleListener
 */
class AbstractMediaFolderGroupRoleListener
{
    use ContainerAwareTrait;

    protected $mediaFolderGroupRoleClass;

    /**
     * @param string $mediaFolderGroupRoleClass
     */
    public function __construct($mediaFolderGroupRoleClass)
    {
        $this->mediaFolderGroupRoleClass = $mediaFolderGroupRoleClass;
    }

    /**
     * @return array
     */
    protected function getMediaFolderRoles()
    {
        $collector = $this->container->get('open_orchestra_backoffice.collector.backoffice_role');

        return $collector->getRolesByType('media|media_folder');
    }

    /**
     * @param FolderInterface $folder
     *
     * @return string
     */
    protected function getFolderAccessType(FolderInterface $folder)
    {
        $accessType = ModelGroupRoleInterface::ACCESS_INHERIT;
        if (null === $folder->getParent()) {
            $accessType = ModelGroupRoleInterface::ACCESS_GRANTED;
        }

        return $accessType;
    }

    /**
     * @param FolderInterface $folder
     * @param GroupInterface  $group
     * @param string          $role
     * @param string          $accessType
     *
     * @return ModelGroupRoleInterface
     */
    protected function createMediaFolderGroupRole(FolderInterface $folder, GroupInterface $group, $role, $accessType)
    {
        /** @var $mediaFolderGroupRole ModelGroupRoleInterface */
        $mediaFolderGroupRole = new $this->mediaFolderGroupRoleClass();
        $mediaFolderGroupRole->setType(FolderInterface::GROUP_ROLE_TYPE);
        $mediaFolderGroupRole->setId($folder->getId());
        $mediaFolderGroupRole->setRole($role);
        $mediaFolderGroupRole->setAccessType($accessType);
        $isGranted = (ModelGroupRoleInterface::ACCESS_DENIED === $accessType) ? false : true;
        if (ModelGroupRoleInterface::ACCESS_INHERIT === $accessType) {
            $parentFolderRole = $group->getModelGroupRoleByTypeAndIdAndRole(FolderInterface::GROUP_ROLE_TYPE, $folder->getParent()->getId(), $role);
            if ($parentFolderRole instanceof ModelGroupRoleInterface) {
                $isGranted = $parentFolderRole->isGranted();
            }
        }
        $mediaFolderGroupRole->setGranted($isGranted);

        return $mediaFolderGroupRole;
    }
}
