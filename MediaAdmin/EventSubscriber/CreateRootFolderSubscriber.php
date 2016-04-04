<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\ModelInterface\Event\SiteEvent;
use OpenOrchestra\ModelInterface\SiteEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CreateRootFolderSubscriber
 */
class CreateRootFolderSubscriber implements EventSubscriberInterface
{

    protected $repositoryTemplate;
    protected $objectManager;
    protected $translator;
    protected $folderClass;

    /**
     * @param ObjectManager       $objectManager
     * @param TranslatorInterface $translator
     * @param string              $folderClass
     */
    public function __construct(
        ObjectManager $objectManager,
        TranslatorInterface $translator,
        $folderClass
    ){
        $this->objectManager = $objectManager;
        $this->translator = $translator;
        $this->folderClass = $folderClass;
    }

    /**
     * @param SiteEvent $siteEvent
     */
    public function createRootFolder(SiteEvent $siteEvent)
    {
        $site = $siteEvent->getSite();
        /** @var FolderInterface $folder */
        $folder = new $this->folderClass();
        $folder->setSiteId($site->getSiteId());
        $name = $this->translator->trans('open_orchestra_media_admin.folder.root_name');
        $folder->setName($name);
        $this->objectManager->persist($folder);
        $this->objectManager->flush();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            SiteEvents::SITE_CREATE => 'createRootFolder',
        );
    }
}
