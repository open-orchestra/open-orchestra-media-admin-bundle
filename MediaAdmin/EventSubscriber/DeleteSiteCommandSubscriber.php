<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use OpenOrchestra\Backoffice\Command\OrchestraDeleteSiteTools;
use OpenOrchestra\Backoffice\Event\SiteCommandEvent;
use OpenOrchestra\Backoffice\SiteCommandEvents;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DeleteSiteCommandSubscriber
 */
class DeleteSiteCommandSubscriber implements EventSubscriberInterface
{
    protected $mediaRepository;
    protected $folderRepository;
    protected $deleteSiteTools;
    protected $mediaClass;

    /**
     * @param MediaRepositoryInterface  $mediaRepository
     * @param FolderRepositoryInterface $folderRepository
     * @param OrchestraDeleteSiteTools  $deleteSiteTools
     * @param string                    $mediaClass
     */
    public function __construct(
        MediaRepositoryInterface $mediaRepository,
        FolderRepositoryInterface $folderRepository,
        OrchestraDeleteSiteTools $deleteSiteTools,
        $mediaClass
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->folderRepository = $folderRepository;
        $this->deleteSiteTools = $deleteSiteTools;
        $this->mediaClass = $mediaClass;
    }

    /**
     * @param SiteCommandEvent $siteEvent
     */
    public function checkHardDeleteSite(SiteCommandEvent $siteEvent)
    {
        $siteId = $siteEvent->getSite()->getSiteId();
        $io = $siteEvent->getIo();

        $medias = $this->mediaRepository->findWithUseReferences($siteId);
        $usageMedias = $this->deleteSiteTools->findUsageReferenceInOtherSite($siteId, $medias);
        if (!empty($usageMedias)) {
            $io->section('Usage of media in other sites');
            $this->deleteSiteTools->displayUsedReferences($io, $usageMedias);
            throw new \RuntimeException('You should remove usage of nodes before remove site '.$siteId);
        }
    }

    /**
     * @param SiteCommandEvent $siteEvent
     */
    public function deleteMedia(SiteCommandEvent $siteEvent)
    {
        $siteId = $siteEvent->getSite()->getSiteId();
        $io = $siteEvent->getIo();

        $io->comment('Remove use references of medias');
        $this->deleteSiteTools->removeUseReferenceEntity($siteId, $this->mediaClass);

        $io->comment('Remove medias');
        $this->mediaRepository->removeAllBySiteId($siteId);

        $io->comment('Remove folders');
        $this->folderRepository->removeAllBySiteId($siteId);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            SiteCommandEvents::SITE_CHECK_HARD_DELETE => 'checkHardDeleteSite',
            SiteCommandEvents::SITE_HARD_DELETE => 'deleteMedia',
        );
    }
}
