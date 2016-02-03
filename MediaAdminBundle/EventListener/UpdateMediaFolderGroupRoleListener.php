<?php

namespace OpenOrchestra\MediaAdminBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use OpenOrchestra\BackofficeBundle\Model\GroupInterface;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface;
use OpenOrchestra\MediaAdminBundle\Exceptions\MediaFolderGroupRoleNotFoundException;


class UpdateMediaFolderGroupRoleListener
{
    protected $folderClass;

    /**
     * @param string $folderClass
     */
    public function __construct($folderClass)
    {
        $this->folderClass = $folderClass;
    }

    /**
     * @param PreUpdateEventArgs $event
     *
     * @throws MediaFolderGroupRoleNotFoundException
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $document = $event->getDocument();
        $uow = $event->getDocumentManager()->getUnitOfWork();
        if (
            $document instanceof MediaFolderGroupRoleInterface &&
            $event->hasChangedField("accessType")
        ) {
            $parentAssociation = $uow->getParentAssociation($document);
            /** @var $group GroupInterface */
            if (isset($parentAssociation[1]) && ($group = $parentAssociation[1]) instanceof GroupInterface) {
                $folderRepository = $event->getDocumentManager()->getRepository($this->folderClass);
                $folders = $folderRepository->findByParent($document->getFolderId());
                /** @var $folder FolderInterface */
                foreach ($folders as $folder) {
                    $role = $document->getRole();
                    $mediaFolderGroupRole = $group->getMediaFolderRoleByMediaFolderAndRole($folder->getId(), $role);
                    if( $mediaFolderGroupRole === null) {
                        throw new MediaFolderGroupRoleNotFoundException($role, $folder->getName(), $group->getName());
                    } else if (MediaFolderGroupRoleInterface::ACCESS_INHERIT === $mediaFolderGroupRole->getAccessType()) {
                        $mediaFolderGroupRole->setGranted($document->isGranted());
                    }
                    throw new MediaFolderGroupRoleNotFoundException($role, $folder->getName(), $group->getName());
                }
                $meta = $event->getDocumentManager()->getClassMetadata(get_class($group));
                $uow->recomputeSingleDocumentChangeSet($meta, $group);
            }
        }
    }
}
