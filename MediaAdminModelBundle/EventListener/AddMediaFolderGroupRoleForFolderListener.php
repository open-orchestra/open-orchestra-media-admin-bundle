<?php

namespace OpenOrchestra\MediaAdminModelBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use OpenOrchestra\Backoffice\Model\GroupInterface;
use OpenOrchestra\Media\Model\FolderInterface;

/**
 * Class AddMediaFolderGroupRoleForFolderListener
 */
class AddMediaFolderGroupRoleForFolderListener extends AbstractMediaFolderGroupRoleListener
{
    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $document = $event->getDocument();
        if ($document instanceof FolderInterface) {
            $accessType = $this->getFolderAccessType($document);
            $sites = $document->getSites();
            $siteIds = array();
            if (!empty($sites)) {
                foreach ($sites as $element){
                    $siteIds[] = $element['siteId'];
                }
            }
            $groups = $this->container->get('open_orchestra_user.repository.group')->findAllWithSite();
            $mediaFolderRoles = $this->getMediaFolderRoles();
            /** @var GroupInterface $group */
            foreach ($groups as $group) {
                if (empty($sites) || in_array($group->getSite()->getSiteId(), $siteIds)) {
                    foreach ($mediaFolderRoles as $role => $translation) {
                        if (false === $group->hasModelGroupRoleByTypeAndIdAndRole(FolderInterface::GROUP_ROLE_TYPE, $document->getId(), $role)) {
                            $mediaFolderRole = $this->createMediaFolderGroupRole($document, $group, $role, $accessType);
                            $group->addModelGroupRole($mediaFolderRole);
                            $event->getDocumentManager()->persist($group);
                            $event->getDocumentManager()->flush($group);
                        }
                    }
                }
            }
        }
    }
}
