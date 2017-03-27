<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ClientConfigurationCompilerPass
 */
class ClientConfigurationCompilerPass implements CompilerPassInterface
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
        $clientConfiguration = $container->getDefinition('open_orchestra_backoffice.manager.client_configuration');
        $clientConfiguration->addMethodCall('addClientConfiguration', array('media_filter_type', $container->getParameter('open_orchestra_media_admin.media_type_filter')));

        if ($container->hasParameter('open_orchestra_media.allowed_mime_type')) {
            $allowedMineType = $container->getParameter('open_orchestra_media.allowed_mime_type');
            $clientConfiguration->addMethodCall('addClientConfiguration', array('allowed_mime_types', $allowedMineType));
        }

    }
}
