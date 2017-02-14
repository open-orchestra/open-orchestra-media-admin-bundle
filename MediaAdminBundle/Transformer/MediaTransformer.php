<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use OpenOrchestra\MediaAdminBundle\Context\MediaAdminGroupContext;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;

/**
 * Class MediaTransformer
 */
class MediaTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $fileAlternativesManager;
    protected $multiLanguageChoiceManager;
    protected $mediaDomain;
    protected $mediaRepository;

    /**
     * @param string                               $facadeClass
     * @param FileAlternativesManager              $fileAlternativesManager
     * @param MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
     * @param string                               $mediaDomain
     * @param AuthorizationCheckerInterface        $authorizationChecker
     * @param MediaRepositoryInterface             $mediaRepository
     */
    public function __construct(
        $facadeClass,
        FileAlternativesManager $fileAlternativesManager,
        MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager,
        $mediaDomain,
        AuthorizationCheckerInterface $authorizationChecker,
        MediaRepositoryInterface $mediaRepository
    ) {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->fileAlternativesManager = $fileAlternativesManager;
        $this->multiLanguageChoiceManager = $multiLanguageChoiceManager;
        $this->mediaDomain = $mediaDomain;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @param MediaInterface $mixed
     *
     * @return FacadeInterface
     */
    public function transform($mixed)
    {
        $facade = $this->newFacade();

        $facade->id = $mixed->getId();
        $facade->name = $mixed->getName();
        $facade->mimeType = $mixed->getMimeType();

        $mediaFolder = $mixed->getMediaFolder();
        $facade->alt = $this->multiLanguageChoiceManager->choose($mixed->getAlts());
        $facade->title = $this->multiLanguageChoiceManager->choose($mixed->getTitles());
        $facade->original = $this->generateMediaUrl($mixed->getFilesystemName());
        $facade->thumbnail = $this->generateMediaUrl($mixed->getThumbnail());
        $facade->mediaType = $mixed->getMediaType();

        if ($this->hasGroup(MediaAdminGroupContext::MEDIA_ALTERNATIVES)) {
            $alternatives = $mixed->getAlternatives();
            foreach ($alternatives as $format => $alternativeName) {
                $facade->addAlternative($format, $this->generateMediaUrl($alternativeName));
            }
        }

        $facade->isEditable = false;

        if ($this->authorizationChecker->isGranted(ContributionActionInterface::EDIT, $mediaFolder)) {
            $facade->isEditable = true;
        }

        $facade->addRight(
            'can_edit',
            $this->authorizationChecker->isGranted(ContributionActionInterface::EDIT, $mixed)
        );
        $facade->addRight(
            'can_delete',
            $this->authorizationChecker->isGranted(ContributionActionInterface::DELETE, $mixed) && !$mixed->isUsed()
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
        if (null !== $facade->id) {
            return $this->mediaRepository->find($facade->id);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media';
    }

    /**
     * @param $key
     *
     * @return null|string
     */
    protected function generateMediaUrl($key)
    {
        if ($key === null) {
            return null;
        } else {
            $route = $this->generateRoute('open_orchestra_media_get',
                array('key' => $key),
                UrlGeneratorInterface::ABSOLUTE_PATH);

            return '//' . $this->mediaDomain . $route;
        }
    }
}
