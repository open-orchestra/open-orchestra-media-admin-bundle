<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\GroupBundle\Event\GroupFacadeEvent;
use OpenOrchestra\GroupBundle\GroupFacadeEvents;
use OpenOrchestra\ModelInterface\Model\ReadSiteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class MediaFolderGroupTransformSubscriber
 */
class MediaFolderGroupTransformSubscriber implements EventSubscriberInterface
{
    protected $router;

    /**
     * @param UrlGeneratorInterface           $router
     */
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param GroupFacadeEvent $event
     */
    public function postGroupTransform(GroupFacadeEvent $event)
    {
        $facade = $event->getGroupFacade();
        $group = $event->getGroup();

        $facade->addLink('_self_panel_media_folder_tree', $this->router->generate(
            'open_orchestra_api_group_show',
            array('groupId' => $group->getId()),
            UrlGeneratorInterface::ABSOLUTE_URL
        ));

        if ($group->getSite() instanceof ReadSiteInterface) {
            $facade->addLink('_self_folder_tree', $this->router->generate(
                'open_orchestra_api_folder_list_tree',
                array('siteId' => $group->getSite()->getSiteId()),
                UrlGeneratorInterface::ABSOLUTE_URL
            ));
            $facade->addLink('_role_list_media_folder', $this->router->generate(
                'open_orchestra_api_role_list_by_type',
                array('type' => 'media|media_folder'),
                UrlGeneratorInterface::ABSOLUTE_URL
            ));
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            GroupFacadeEvents::POST_GROUP_TRANSFORMATION => 'postGroupTransform',
        );
    }
}
