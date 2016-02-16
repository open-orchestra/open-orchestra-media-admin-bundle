<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\GroupBundle\Event\GroupFacadeEvent;
use OpenOrchestra\GroupBundle\GroupFacadeEvents;
use OpenOrchestra\MediaAdminBundle\Transformer\MediaFolderGroupRoleTransformer;
use OpenOrchestra\ModelInterface\Model\ReadSiteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class MediaFolderGroupTransformSubscriber
 */
class MediaFolderGroupTransformSubscriber implements EventSubscriberInterface
{
    protected $router;
    protected $transformer;

    /**
     * @param UrlGeneratorInterface $router
     */
    public function __construct(UrlGeneratorInterface $router, MediaFolderGroupRoleTransformer $transformer)
    {
        $this->router = $router;
        $this->transformer = $transformer;
    }

    /**
     * @param GroupFacadeEvent $event
     */
    public function postGroupTransformation(GroupFacadeEvent $event)
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
     * @param GroupFacadeEvent $event
     */
    public function postGroupReverseTransformation(GroupFacadeEvent $event)
    {
        $facade = $event->getGroupFacade();
        $group = $event->getGroup();

        foreach ($facade->getDocumentRoles() as $documentRoleFacade) {
            if ('folder' === $documentRoleFacade->type) {
                $source = $group->getDocumentRoleByTypeAndIdAndRole(
                    $documentRoleFacade->type,
                    $documentRoleFacade->document,
                    $documentRoleFacade->name
                );
                $documentGroupRole = $this->transformer->reverseTransformWithGroup($group, $documentRoleFacade, $source);
                $group->addDocumentRole($documentGroupRole);
            }
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            GroupFacadeEvents::POST_GROUP_TRANSFORMATION => 'postGroupTransformation',
            GroupFacadeEvents::POST_GROUP_REVERSE_TRANSFORMATION => 'postGroupReverseTransformation'
        );
    }
}
