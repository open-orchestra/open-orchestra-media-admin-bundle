<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use OpenOrchestra\Backoffice\Event\SiteFormEvent;
use OpenOrchestra\Backoffice\SiteFormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AddChoiceSiteShareMediaSubscriber
 */
class AddChoiceSiteShareMediaSubscriber implements EventSubscriberInterface
{
    protected $mediaLibrarySharingSubscriber;

    /**
     * @param EventSubscriberInterface $mediaLibrarySharingSubscriber
     */
    public function __construct(EventSubscriberInterface $mediaLibrarySharingSubscriber)
    {
        $this->mediaLibrarySharingSubscriber = $mediaLibrarySharingSubscriber;
    }

    /**
     * add choice site to site form
     *
     * @param SiteFormEvent $event
     */
    public function addChoiceSite(SiteFormEvent $event)
    {
        $builder = $event->getBuilder();
        $subGroupRender = $builder->getAttribute('sub_group_render');
        $subGroupRender = array_merge($subGroupRender, array(
            'media' => array(
                'rank' => 2,
                'label' => 'open_orchestra_media_admin.form.site.sub_group.media',
            ),
        ));
        $builder->setAttribute('sub_group_render', $subGroupRender);

        $builder->addEventSubscriber($this->mediaLibrarySharingSubscriber);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            SiteFormEvents::SITE_FORM_CREATION => 'addChoiceSite',
        );
    }
}
