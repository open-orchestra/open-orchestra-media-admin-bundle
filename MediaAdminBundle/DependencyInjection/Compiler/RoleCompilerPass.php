<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler;

use OpenOrchestra\BackofficeBundle\DependencyInjection\Compiler\AbstractRoleCompilerPass;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class RoleCompilerPass
 */
class RoleCompilerPass extends AbstractRoleCompilerPass
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $this->addRoles($container, array(
            TreeFolderPanelStrategy::ROLE_ACCESS_MEDIA_FOLDER,
            TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER,
            TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER,
            TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA_FOLDER,
            TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA,
            TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA,
            TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA,
        ));

        if ($container->hasParameter('open_orchestra_backoffice.role')) {
            $roles = $container->getParameter('open_orchestra_backoffice.role');
            if ($container->hasParameter('open_orchestra_media.role')) {
                $roles = array_merge_recursive($roles, $container->getParameter('open_orchestra_media.role'));
            }
            $container->setParameter('open_orchestra_backoffice.role', $roles);
        }
    }
}
