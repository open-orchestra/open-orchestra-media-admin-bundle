<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler;

use OpenOrchestra\BaseBundle\DependencyInjection\Compiler\AbstractTaggedCompiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MediaFormCompilerPass
 */
class MediaFormCompilerPass extends AbstractTaggedCompiler implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $managerName = 'open_orchestra_media_admin.media_form.manager';
        $tagName = 'open_orchestra_media_admin.media_form.strategy';

        $this->addStrategyToManager($container, $managerName, $tagName);
    }
}
