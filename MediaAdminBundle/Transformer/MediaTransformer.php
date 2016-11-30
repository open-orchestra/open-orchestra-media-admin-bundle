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

/**
 * Class MediaTransformer
 */
class MediaTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $fileAlternativesManager;
    protected $multiLanguageChoiceManager;
    protected $mediaDomain;

    /**
     * @param string                               $facadeClass
     * @param FileAlternativesManager              $fileAlternativesManager
     * @param MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
     * @param string                               $mediaDomain
     * @param AuthorizationCheckerInterface        $authorizationChecker
     */
    public function __construct(
        $facadeClass,
        FileAlternativesManager $fileAlternativesManager,
        MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager,
        $mediaDomain,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($facadeClass, $authorizationChecker);
        $this->fileAlternativesManager = $fileAlternativesManager;
        $this->multiLanguageChoiceManager = $multiLanguageChoiceManager;
        $this->mediaDomain = $mediaDomain;
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
        $facade->isDeletable = !$mixed->isUsed()
            && $this->authorizationChecker->isGranted(ContributionActionInterface::DELETE, $mediaFolder);
        $facade->alt = $this->multiLanguageChoiceManager->choose($mixed->getAlts());
        $facade->title = $this->multiLanguageChoiceManager->choose($mixed->getTitles());
        $facade->original = $this->generateMediaUrl($mixed->getFilesystemName());
        $facade->thumbnail = $this->generateMediaUrl($mixed->getThumbnail());

        if ($this->hasGroup(MediaAdminGroupContext::MEDIA_ALTERNATIVES)) {
            $alternatives = $mixed->getAlternatives();
            foreach ($alternatives as $format => $alternativeName) {
                $facade->addAlternative($format, $this->generateMediaUrl($alternativeName));
                $facade->addLink('_self_format_' . $format,
                    $this->generateRoute('open_orchestra_media_admin_media_override',
                        array('format' => $format, 'mediaId' => $mixed->getId())
                    )
                );
            }
        }

        $facade->isEditable = false;

        if ($this->authorizationChecker->isGranted(ContributionActionInterface::EDIT, $mediaFolder)) {
            $facade->isEditable = true;
        }

        $facade->addLink('_self_select', $mixed->getId());
        $facade->addLink('_api_full', $this->generateRoute('open_orchestra_api_media_show', array(
            'mediaId' => $mixed->getId()
        )));

        if ($this->hasGroup(MediaAdminGroupContext::MEDIA_ADVANCED_LINKS)) {

            $facade->addLink('_self_select_format', $this->generateRoute('open_orchestra_media_admin_media_select_format', array(
                'mediaId' => $mixed->getId()
            )));

            if ($facade->isEditable) {
                $facade->addLink('_self_crop', $this->generateRoute('open_orchestra_media_admin_media_crop', array(
                    'mediaId' => $mixed->getId()
                )));

                $facade->addLink('_self_meta', $this->generateRoute('open_orchestra_media_admin_media_meta', array(
                    'mediaId' => $mixed->getId()
                )));
            }
        }

        if ($facade->isDeletable) {
            $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_media_delete', array(
                'mediaId' => $mixed->getId()
            )));
        }

        return $facade;
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
