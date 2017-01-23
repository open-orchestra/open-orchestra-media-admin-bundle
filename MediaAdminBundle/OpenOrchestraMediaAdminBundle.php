<?php

namespace OpenOrchestra\MediaAdminBundle;

use OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler\FieldToElasticaTypeCompilerPass;
use OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler\FileAlternativesCompilerPass;
use OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler\ExtractReferenceCompilerPass;
use OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler\TwigGlobalsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class OpenOrchestraMediaAdminBundle
 */
class OpenOrchestraMediaAdminBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ExtractReferenceCompilerPass());
        $container->addCompilerPass(new TwigGlobalsCompilerPass());
        $container->addCompilerPass(new FileAlternativesCompilerPass());
        $container->addCompilerPass(new FieldToElasticaTypeCompilerPass());
    }
}
