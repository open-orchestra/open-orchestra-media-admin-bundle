<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class FieldToElasticaTypeCompilerPass
 */
class FieldToElasticaTypeCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('open_orchestra_elastica_admin.mapper.form')) {
            return ;
        }

        $definition = $container->getDefinition('open_orchestra_elastica_admin.mapper.form');
        $definition->addMethodCall('addMappingConfiguration', array('orchestra_media', 'object'));
    }
}
