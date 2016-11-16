<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
     * @return array
     */
    protected function getSupportedAttributes()
    {
        return array(
            ContributionActionInterface::READ,
            ContributionActionInterface::ADD,
            ContributionActionInterface::EDIT,
            ContributionActionInterface::DELETE
        );
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (ContributionActionInterface::READ == $attribute) {
            return $this->isSubjectInAllowedPerimeter($subject->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
        }

        if ($subject->getCreatedBy() == $user->getUsername()) {
            return $this->voteForOwnedMediaFolder($attribute, $subject, $user);
        }

        return $this->voteForSomeoneElseMediaFolder($attribute, $subject, $user);
    }

    /**
     * Vote for $action on $folder owned by $user
     *
     * @param string               $action
     * @param MediaFolderInterface $folder
     * @param UserInterface        $user
     *
     * @return bool
     */
    protected function voteForOwnedMediaFolder($action, MediaFolderInterface $folder, UserInterface $user)
    {
        return $user->hasRole(ContributionRoleInterface::MEDIA_FOLDER_CONTRIBUTOR)
            && $this->isSubjectInAllowedPerimeter($folder->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $folder not owned by $user
     *
     * @param string               $action
     * @param MediaFolderInterface $folder
     * @param UserInterface        $user
     *
     * @return bool
     */
    protected function voteForSomeoneElseMediaFolder($action, MediaFolderInterface $folder, UserInterface $user)
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
