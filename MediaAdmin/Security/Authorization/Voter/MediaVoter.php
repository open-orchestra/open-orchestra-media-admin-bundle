<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\UserBundle\Model\UserInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface;
use OpenOrchestra\Backoffice\Security\Authorization\Voter\AbstractEditorialVoter;

/**
 * Class MediaVoter
 *
 * Voter checking rights on media management
 */
class MediaVoter extends AbstractEditorialVoter
{
    /**
     * @return array
     */
    protected function getSupportedClasses()
    {
        return array('OpenOrchestra\Media\Model\MediaInterface');
    }

    /**
     * Vote for Read action
     * A user can read a media if it is located into a folder is in his perimeter
     *
     * @param MediaInterface $media
     * @param UserInterface  $user
     *
     * @return bool
     */
    protected function voteForReadAction($media, UserInterface $user)
    {
        return $this->isSubjectInPerimeter($media->getMediaFolder()->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $media owned by $user
     * A user can act on his own medias if he has the MEDIA_CONTRIBUTOR role and the media is located into a folder in his perimeter 
     *
     * @param string         $action
     * @param MediaInterface $folder
     * @param UserInterface  $user
     *
     * @return bool
     */
    protected function voteForOwnedSubject($action, $media, UserInterface $user)
    {
        return $user->hasRole(ContributionRoleInterface::MEDIA_CONTRIBUTOR)
            && $this->isSubjectInPerimeter($media->getMediaFolder()->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $folder not owned by $user
     * A user can act on someone else's media if he has the matching super role and the media is located into a folder is in his perimeter
     *
     * @param string         $action
     * @param MediaInterface $media
     * @param UserInterface  $user
     *
     * @return bool
     */
    protected function voteForSomeoneElseSubject($action, $media, UserInterface $user)
    {
        $requiredRole = ContributionRoleInterface::MEDIA_CONTRIBUTOR;

        switch ($action) {
            case ContributionActionInterface::EDIT:
                $requiredRole = ContributionRoleInterface::MEDIA_SUPER_EDITOR;
            break;
            case ContributionActionInterface::DELETE:
                $requiredRole = ContributionRoleInterface::MEDIA_SUPER_SUPRESSOR;
            break;
        }

        return $user->hasRole($requiredRole)
            && $this->isSubjectInPerimeter($media->getMediaFolder()->getPath(), $user, MediaFolderInterface::ENTITY_TYPE);
    }
}
