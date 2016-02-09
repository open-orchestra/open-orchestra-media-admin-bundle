<?php

namespace OpenOrchestra\MediaAdmin\Security\Authorization\Voter;

use FOS\UserBundle\Model\UserInterface;
use OpenOrchestra\BackofficeBundle\Model\GroupInterface;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\Media\Model\MediaFolderGroupRoleInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\ModelInterface\Model\ReadSiteInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class MediaFolderGroupRoleVoter
 */
class MediaFolderGroupRoleVoter implements VoterInterface
{
    /**
     * @var FolderRepositoryInterface
     */
    protected $folderRepository;

    /**
     * @param FolderRepositoryInterface $folderRepository
     */
    public function __construct(FolderRepositoryInterface $folderRepository)
    {
        $this->folderRepository = $folderRepository;
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return (bool) preg_match('/^ROLE_ACCESS_[^_]+_MEDIA|MEDIA_FOLDER$/', $attribute);
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool true if this Voter can process the class
     */
    public function supportsClass($class)
    {
         return is_subclass_of($class, 'OpenOrchestra\Media\Model\FolderInterface');
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param FolderInterface|null $object The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass($object)) {
            return self::ACCESS_ABSTAIN;
        }
        if (($user = $token->getUser()) instanceof UserInterface && $user->isSuperAdmin()) {
            return VoterInterface::ACCESS_GRANTED;
        }
        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                return self::ACCESS_ABSTAIN;
            }
        }

        /** @var GroupInterface $group */
        foreach ($user->getGroups() as $group) {
            if (!$group->getSite() instanceof ReadSiteInterface) {
                continue;
            }

            if (!$object->hasSite($group->getSite()->getSiteId())) {
                continue;
            }
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute)) {
                    continue;
                }
                if (true === $this->isGrantedMediaFolderGroupRole($object, $group, $attribute)) {
                    return self::ACCESS_GRANTED;
                }
            }

            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * @param FolderInterface $folder
     * @param GroupInterface  $group
     * @param string          $attribute
     *
     * @return boolean
     */
    protected function isGrantedMediaFolderGroupRole(FolderInterface $folder, GroupInterface $group, $attribute)
    {
        $mediaFolderGroupRole = $group->getMediaFolderRoleByMediaFolderAndRole($folder->getId(), $attribute);

        if ($mediaFolderGroupRole instanceof MediaFolderGroupRoleInterface) {
            return $mediaFolderGroupRole->isGranted();
        }

        return false;
    }
}
