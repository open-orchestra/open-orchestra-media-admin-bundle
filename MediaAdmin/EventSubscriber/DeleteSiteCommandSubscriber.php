<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use OpenOrchestra\Backoffice\Event\SiteCommandEvent;
use OpenOrchestra\Backoffice\SiteCommandEvents;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DeleteSiteCommandSubscriber
 */
class DeleteSiteCommandSubscriber implements EventSubscriberInterface
{
    protected $mediaRepository;

    /**
     * DeleteSiteCommandSubscriber constructor.
     * @param MediaRepositoryInterface $mediaRepository
     */
    public function __construct(MediaRepositoryInterface $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @param SiteCommandEvent $siteEvent
     */
    public function checkHardDeleteSite(SiteCommandEvent $siteEvent)
    {
        $siteId = $siteEvent->getSite()->getSiteId();
        $medias = $this->mediaRepository->findWithUseReferences($siteId);

        throw  new \RuntimeException('test');
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            SiteCommandEvents::SITE_CHECK_HARD_DELETE => 'checkHardDeleteSite',
        );
    }
}
