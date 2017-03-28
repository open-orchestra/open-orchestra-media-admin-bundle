<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface as MediaRoleInterface;
use OpenOrchestra\Backoffice\Security\ContributionRoleInterface;

/**
 * Class MediaCollectionTransformer
 */
class MediaCollectionTransformer extends AbstractSecurityCheckerAwareTransformer
{
    /** @var  FolderRepositoryInterface */
    protected $folderRepository;
    protected $user;

    /**
     * @param string                        $facadeClass
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FolderRepositoryInterface     $folderRepository
     */
    public function __construct(
        $facadeClass,
        AuthorizationCheckerInterface $authorizationChecker,
        FolderRepositoryInterface $folderRepository,
        TokenStorage $tokenStorage
    ) {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->folderRepository = $folderRepository;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param ArrayCollection $mixed
     *
     * @return FacadeInterface
     */
    public function transform($mixed)
    {
        $facade = $this->newFacade();

        foreach ($mixed as $media) {
            $facade->addMedia($this->getTransformer('media')->transform($media));
        }

        $facade->addRight(
            'can_create',
            $this->user->hasRole(MediaRoleInterface::MEDIA_CONTRIBUTOR)
            || $this->user->hasRole(ContributionRoleInterface::PLATFORM_ADMIN)
            || $this->user->hasRole(ContributionRoleInterface::DEVELOPER)
        );

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param null $source
     *
     * @return FacadeInterface|null
     */
    public function reverseTransform(FacadeInterface $facade, $source = null)
    {
        $medias = array();
        $mediasFacade = $facade->getMedias();
        foreach ($mediasFacade as $mediaFacade) {
            $media = $this->getTransformer('media')->reverseTransform($mediaFacade);
            if (null !== $media) {
                $medias[] = $media;
            }
        }

        return $medias;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_collection';
    }
}
