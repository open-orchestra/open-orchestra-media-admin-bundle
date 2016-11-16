<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\UserBundle\Model\UserInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface;

/**
 * Class MediaFolderVoter
 *
 * Voter checking rights on media folder management
 */
class MediaFolderVoter extends AbstractPerimeterVoter
{
    /**
     * @return array
     */
    protected function getSupportedClasses()
    {
        return array('OpenOrchestra\Media\Model\MediaFolderInterface');
    }

    /**
     * Vote for Read action
     * A user can read a folder if it is in his perimeter
     *
     * @param MediaFolderInterface $folder
     * @param UserInterface        $user
     *
     * @return bool
     */
    protected function voteForReadAction($folder, $user)
    {
        return $this->isSubjectInAllowedPerimeter($subject->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $folder owned by $user
     * A user can act on his own folders if he has the MEDIA_FOLDER_CONTRIBUTOR role and the folder is in his perimeter 
     *
     * @param string               $action
     * @param MediaFolderInterface $folder
     * @param UserInterface        $user
     *
     * @return bool
     */
    protected function voteForOwnedSubject($action, $folder, UserInterface $user)
    {
        return $user->hasRole(ContributionRoleInterface::MEDIA_FOLDER_CONTRIBUTOR)
            && $this->isSubjectInAllowedPerimeter($folder->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $folder not owned by $user
     * A user can act on someone else's folder if he has the matching super role and the folder is in his perimeter
     *
     * @param string               $action
     * @param MediaFolderInterface $folder
     * @param UserInterface        $user
     *
     * @return bool
     */
    protected function voteForSomeoneElseSubject($action, $folder, UserInterface $user)
    {
        $requiredRole = ContributionRoleInterface::MEDIA_FOLDER_CONTRIBUTOR;

        switch ($action) {
            case ContributionActionInterface::EDIT:
                $requiredRole = ContributionRoleInterface::MEDIA_FOLDER_SUPER_EDITOR;
            break;
            case ContributionActionInterface::DELETE:
                $requiredRole = ContributionRoleInterface::MEDIA_FOLDER_SUPER_SUPRESSOR;
            break;
        }

        return $user->hasRole($requiredRole)
            && $this->isSubjectInAllowedPerimeter($folder->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
    }
}
