<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
            && $this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA, $mediaFolder);
        $facade->alt = $this->multiLanguageChoiceManager->choose($mixed->getAlts());
        $facade->title = $this->multiLanguageChoiceManager->choose($mixed->getTitles());
        $facade->original = $this->generateMediaUrl($mixed->getFilesystemName());
        $facade->thumbnail = $this->generateMediaUrl($mixed->getThumbnail());

        $alternatives = $mixed->getAlternatives();
        foreach ($alternatives as $format => $alternativeName) {
            $facade->addAlternative($format, $this->generateMediaUrl($alternativeName));
            $facade->addLink('_self_format_' . $format,
                $this->generateRoute('open_orchestra_media_admin_media_override',
                    array('format' => $format, 'mediaId' => $mixed->getId())
                )
            );
        }

        $facade->addLink('_self_select', $mixed->getId());

        $facade->addLink('_self_select_format', $this->generateRoute('open_orchestra_media_admin_media_select_format', array(
            'mediaId' => $mixed->getId()
        )));

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA, $mediaFolder)) {
            $facade->addLink('_self_crop', $this->generateRoute('open_orchestra_media_admin_media_crop', array(
                'mediaId' => $mixed->getId()
            )));

            $facade->addLink('_self_meta', $this->generateRoute('open_orchestra_media_admin_media_meta', array(
                'mediaId' => $mixed->getId()
            )));
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
