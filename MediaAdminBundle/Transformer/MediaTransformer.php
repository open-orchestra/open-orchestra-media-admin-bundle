<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\MediaAdmin\FileAlternatives\FileAlternativesManager;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use OpenOrchestra\Backoffice\Manager\TranslationChoiceManager;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\ImageStrategy;
use OpenOrchestra\MediaAdminBundle\Facade\MediaFacade;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class MediaTransformer
 */
class MediaTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $fileAlternativesManager;
    protected $translationChoiceManager;
    protected $mediaDomain;

    /**
     * @param FileAlternativesManager       $fileAlternativesManager
     * @param TranslationChoiceManager      $translationChoiceManager
     * @param string                        $mediaDomain
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        FileAlternativesManager $fileAlternativesManager,
        TranslationChoiceManager $translationChoiceManager,
        $mediaDomain,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct($authorizationChecker);
        $this->fileAlternativesManager = $fileAlternativesManager;
        $this->translationChoiceManager = $translationChoiceManager;
        $this->mediaDomain = $mediaDomain;
    }

    /**
     * @param MediaInterface $mixed
     *
     * @return MediaFacade
     */
    public function transform($mixed)
    {
        $facade = new MediaFacade();

        $facade->id = $mixed->getId();
        $facade->name = $mixed->getName();
        $facade->mimeType = $mixed->getMimeType();
        $facade->isDeletable = $mixed->isDeletable() && $this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA);
        $facade->alt = $this->translationChoiceManager->choose($mixed->getAlts());
        $facade->title = $this->translationChoiceManager->choose($mixed->getTitles());
        $facade->original = $this->generateMediaUrl($mixed->getFilesystemName());
        $facade->thumbnail = $this->generateMediaUrl($mixed->getThumbnail());

        $alternatives = $this->fileAlternativesManager->getAlternatives($mixed);
        foreach ($alternatives as $format => $alternativeName) {
            $facade->addAlternative($format, $this->generateMediaUrl($alternativeName));
                $facade->addLink('_self_format_' . $format,
                $this->generateRoute('open_orchestra_media_admin_media_override',
                    array('format' => $format, 'mediaId' => $mixed->getId())
                )
            );
        }

        $facade->addLink('_self_select', $mixed->getId());

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA)) {
            $facade->addLink('_self_crop', $this->generateRoute('open_orchestra_media_admin_media_crop', array(
                'mediaId' => $mixed->getId()
            )));
        }

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA)) {
            $facade->addLink('_self_meta', $this->generateRoute('open_orchestra_media_admin_media_meta', array(
                'mediaId' => $mixed->getId()
            )));
        }

        if ($this->authorizationChecker->isGranted(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA)) {
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
