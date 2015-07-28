<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\Backoffice\Manager\TranslationChoiceManager;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdminBundle\Facade\MediaFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class MediaTransformer
 */
class MediaTransformer extends AbstractTransformer
{
    protected $thumbnailConfig;
    protected $translationChoiceManager;
    protected $mediaDomain;
    protected $noImageAvailableIcon;
    /**
     * @param array                    $thumbnailConfig
     * @param TranslationChoiceManager $translationChoiceManager
     * @param string                   $mediaDomain
     * @param string                   $noImageAvailableIcon
     */
    public function __construct(array $thumbnailConfig, TranslationChoiceManager $translationChoiceManager, $mediaDomain, $noImageAvailableIcon)
    {
        $this->thumbnailConfig = $thumbnailConfig;
        $this->translationChoiceManager = $translationChoiceManager;
        $this->mediaDomain = $mediaDomain;
        $this->noImageAvailableIcon = $noImageAvailableIcon;
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
        $facade->isDeletable = $mixed->isDeletable();
        $facade->alt = $this->translationChoiceManager->choose($mixed->getAlts());
        $facade->title = $this->translationChoiceManager->choose($mixed->getTitles());

        $facade->displayedImage = $this->generateMediaUrl($mixed->getThumbnail());

        foreach ($this->thumbnailConfig as $format => $thumbnail) {
            $facade->addThumbnail($format, $this->generateMediaUrl($format . '-' . $mixed->getFilesystemName()));
            $facade->addLink('_self_format_' . $format, $this->generateRoute('open_orchestra_media_admin_media_override',
                array('format' => $format, 'mediaId' => $mixed->getId())
            ));
        }

        $facade->addLink('_self_select', $mixed->getId());
        $facade->addLink('_self_crop', $this->generateRoute('open_orchestra_media_admin_media_crop', array(
            'mediaId' => $mixed->getId()
        )));
        $facade->addLink('_self_meta', $this->generateRoute('open_orchestra_media_admin_media_meta', array(
            'mediaId' => $mixed->getId()
        )));
        $facade->addLink('_self_delete', $this->generateRoute('open_orchestra_api_media_delete', array(
            'mediaId' => $mixed->getId()
        )));

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
     * @return string
     */
    protected function generateMediaUrl($key)
    {
        if ($key === null) {
            return $this->noImageAvailableIcon;
        } else {
            $route = $this->generateRoute('open_orchestra_media_get',
                array('key' => $key),
                UrlGeneratorInterface::ABSOLUTE_PATH);
            return $this->mediaDomain . $route;
        }
    }
}
