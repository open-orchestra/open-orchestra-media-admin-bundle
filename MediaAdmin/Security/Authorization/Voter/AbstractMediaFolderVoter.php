<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface;
use OpenOrchestra\Backoffice\Security\Authorization\Voter\AbstractEditorialVoter;
use OpenOrchestra\MediaAdmin\Security\ContributionActionInterface as MediaContributionActionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AbstractMediaFolderVoter
 *
 * Voter checking rights on media folder management
 */
abstract class AbstractMediaFolderVoter extends AbstractEditorialVoter
{
    /**
     * @return array
     */
    protected function getSupportedAttributes()
    {
        return array(
            ContributionActionInterface::READ,
            ContributionActionInterface::CREATE,
            ContributionActionInterface::EDIT,
            ContributionActionInterface::DELETE,
            MediaContributionActionInterface::CREATE_MEDIA_UNDER
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
        if ($this->isSuperAdmin($token)) {
            return true;
        }

        if (ContributionActionInterface::READ === $attribute) {
            return $this->voteForReadAction($subject, $token);
        }

        if ($this->isCreator($subject, $token->getUser())) {
            return $this->voteForOwnedSubject($attribute, $subject, $token);
        }

        if (MediaContributionActionInterface::CREATE_MEDIA_UNDER === $attribute) {
            return $this->voteForCreateMediaAction($subject, $token);
        }

        return $this->voteForSomeoneElseSubject($attribute, $subject, $token);
    }

    /**
     * @param mixed $folder
     *
     * @return string
     */
    abstract protected function getPath($folder);

    /**
     * Vote for Read action
     * A user can read a folder if it is in his perimeter
     *
     * @param mixed          $folder
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteForReadAction($folder, TokenInterface $token)
    {
        return $this->isSubjectInPerimeter($this->getPath($folder), $token->getUser(), MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $folder owned by $user
     * A user can act on his own folders if he has the MEDIA_FOLDER_CONTRIBUTOR role and the folder is in his perimeter 
     *
     * @param string         $action
     * @param mixed          $folder
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteForOwnedSubject($action, $folder, TokenInterface $token)
    {
        return $this->hasRole($token, ContributionRoleInterface::MEDIA_FOLDER_CONTRIBUTOR)
            && $this->isSubjectInPerimeter($this->getPath($folder), $token->getUser(), MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * @param mixed          $folder
     * @param TokenInterface $token
     *
     * @return string
     */
    protected function voteForCreateMediaAction($folder, $token)
    {
        return $this->isSubjectInPerimeter($this->getPath($folder), $token->getUser(), MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $folder not owned by $user
     * A user can act on someone else's folder if he has the matching super role and the folder is in his perimeter
     *
     * @param string         $action
     * @param mixed          $folder
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteForSomeoneElseSubject($action, $folder, TokenInterface $token)
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

        return $this->hasRole($token, $requiredRole)
            && $this->isSubjectInPerimeter($this->getPath($folder), $token->getUser(), MediaFolderInterface::ENTITY_TYPE);
    }
}
