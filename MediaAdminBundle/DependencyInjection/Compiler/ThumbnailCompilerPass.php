<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler;

use OpenOrchestra\BaseBundle\DependencyInjection\Compiler\AbstractTaggedCompiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ThumbnailCompilerPass
 */
class ThumbnailCompilerPass extends AbstractTaggedCompiler implements CompilerPassInterface
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
        $managerName = 'open_orchestra_media_admin.thumbnail_manager';
        $tagName = 'open_orchestra_media_admin.thumbnail.strategy';

        $this->addStrategyToManager($container, $managerName, $tagName);
    }
}
