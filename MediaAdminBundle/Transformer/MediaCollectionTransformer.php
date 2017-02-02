<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class MediaCollectionTransformer
 */
class MediaCollectionTransformer extends AbstractSecurityCheckerAwareTransformer
{
    /** @var  FolderRepositoryInterface */
    protected $folderRepository;

    /**
     * @param string                        $facadeClass
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FolderRepositoryInterface     $folderRepository
     */
    public function __construct(
        $facadeClass,
        AuthorizationCheckerInterface $authorizationChecker,
        FolderRepositoryInterface $folderRepository
    ) {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->folderRepository = $folderRepository;
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
