<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\Backoffice\BusinessRules\BusinessRulesManager;
use OpenOrchestra\Backoffice\BusinessRules\Strategies\BusinessActionInterface;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\Media\Manager\MediaStorageManagerInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface;
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
    protected $mediaRepository;
    protected $businessRulesManager;
    protected $mediaStorageManager;

    /**
     * @param string                               $facadeClass
     * @param FileAlternativesManager              $fileAlternativesManager
     * @param MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
     * @param MediaStorageManagerInterface         $mediaStorageManager
     * @param AuthorizationCheckerInterface        $authorizationChecker
     * @param MediaRepositoryInterface             $mediaRepository
     * @param BusinessRulesManager                 $businessRulesManager
     */
    public function __construct(
        $facadeClass,
        FileAlternativesManager $fileAlternativesManager,
        MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager,
        MediaStorageManagerInterface $mediaStorageManager,
        AuthorizationCheckerInterface $authorizationChecker,
        MediaRepositoryInterface $mediaRepository,
        BusinessRulesManager $businessRulesManager
    ) {
        $this->fileAlternativesManager = $fileAlternativesManager;
        $this->multiLanguageChoiceManager = $multiLanguageChoiceManager;
        $this->mediaRepository = $mediaRepository;
        $this->businessRulesManager = $businessRulesManager;
        $this->mediaStorageManager = $mediaStorageManager;
        parent::__construct($facadeClass, $authorizationChecker);
    }

    /**
     * @param MediaInterface $mixed
     * @param array          $params
     *
     * @return FacadeInterface
     */
    public function transform($mixed, array $params = array())
    {
        $facade = $this->newFacade();

        $facade->id = $mixed->getId();
        $facade->name = $mixed->getName();
        $facade->mimeType = $mixed->getMimeType();

        $mediaFolder = $mixed->getMediaFolder();
        $facade->title = $this->multiLanguageChoiceManager->choose($mixed->getTitles());
        $facade->original = $this->generateMediaUrl($mixed->getFilesystemName());
        $facade->thumbnail = $this->generateMediaUrl($mixed->getThumbnail());
        $facade->mediaType = $mixed->getMediaType();
        $facade->updatedAt = $mixed->getUpdatedAt();

        if ($this->hasGroup(MediaAdminGroupContext::MEDIA_ALTERNATIVES)) {
            $alternatives = $mixed->getAlternatives();
            foreach ($alternatives as $format => $alternativeName) {
                $facade->addAlternative($format, $this->generateMediaUrl($alternativeName));
            }
        }

        foreach ($mixed->getMediaInformations() as $name => $value) {
            $facade->addMediaInformation($name, $value);
        }

        foreach ($mixed->getKeywords() as $keyword) {
            $facade->addKeyword($keyword);
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
            $this->authorizationChecker->isGranted(ContributionActionInterface::DELETE, $mixed) &&
            $this->businessRulesManager->isGranted(BusinessActionInterface::DELETE, $mixed)
        );

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param array           $params
     *
     * @return FacadeInterface|null
     */
    public function reverseTransform(FacadeInterface $facade, array $params = array())
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
            return  $this->mediaStorageManager->getUrl($key);
        }
    }
}
