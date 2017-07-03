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
     * @param array|null      $params
     *
     * @return FacadeInterface
     */
    public function transform($mixed, array $params = null)
    {
        $facade = $this->newFacade();

        foreach ($mixed as $media) {
            $facade->addMedia($this->getContext()->transform('media', $media));
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
     * @param array|null      $params
     *
     * @return FacadeInterface|null
     */
    public function reverseTransform(FacadeInterface $facade, array $params = null)
    {
        $medias = array();
        $mediasFacade = $facade->getMedias();
        foreach ($mediasFacade as $mediaFacade) {
            $media = $this->getContext()->reverseTransform('media', $mediaFacade);
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
