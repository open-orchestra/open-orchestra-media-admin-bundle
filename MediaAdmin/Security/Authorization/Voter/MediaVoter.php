<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface;
use OpenOrchestra\Backoffice\Security\Authorization\Voter\AbstractEditorialVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class MediaVoter
 *
 * Voter checking rights on media management
 */
class MediaVoter extends AbstractEditorialVoter
{
    /**
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supportSubject($subject)
    {
        return $this->supportClasses(
            $subject,
            array('OpenOrchestra\Media\Model\MediaInterface')
        );

    }

    /**
     * Vote for Read action
     * A user can read a media if it is located into a folder is in his perimeter
     *
     * @param MediaInterface $media
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteForReadAction($media, TokenInterface $token)
    {
        return $this->isSubjectInPerimeter($media->getMediaFolder()->getPath(), $token->getUser(), MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $media owned by $user
     * A user can act on his own medias if he has the MEDIA_CONTRIBUTOR role and the media is located into a folder in his perimeter 
     *
     * @param string         $action
     * @param MediaInterface $media
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteForOwnedSubject($action, $media, TokenInterface $token)
    {
        return $this->hasRole($token, ContributionRoleInterface::MEDIA_CONTRIBUTOR)
            && $this->isSubjectInPerimeter($media->getMediaFolder()->getPath(), $token->getUser(), MediaFolderInterface::ENTITY_TYPE);
    }

    /**
     * Vote for $action on $folder not owned by $user
     * A user can act on someone else's media if he has the matching super role and the media is located into a folder is in his perimeter
     *
     * @param string         $action
     * @param MediaInterface $media
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteForSomeoneElseSubject($action, $media, TokenInterface $token)
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

        return $this->hasRole($token, $requiredRole)
            && $this->isSubjectInPerimeter($media->getMediaFolder()->getPath(), $token->getUser(), MediaFolderInterface::ENTITY_TYPE);
    }
}
