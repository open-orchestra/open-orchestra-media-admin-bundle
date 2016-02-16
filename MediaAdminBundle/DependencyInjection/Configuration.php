<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('open_orchestra_media_admin');

        $rootNode->children()
            ->scalarNode('tmp_dir')->defaultValue('/tmp')->end()
            ->arrayNode('thumbnail')->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('max_width')->defaultValue(117)->end()
                    ->scalarNode('max_height')->defaultValue(117)->end()
                    ->scalarNode('compression_quality')->defaultValue(75)->end()
                ->end()
            ->end()
            ->arrayNode('files')->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('alternatives')->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('default')->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('thumbnail')
                                        ->defaultValue('orchestra-media-thumbnail-default.png')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('image')->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('formats')
                                        ->prototype('array')
                                            ->children()
                                                ->integerNode('max_height')->end()
                                                ->integerNode('max_width')->end()
                                                ->integerNode('compression_quality')->isRequired()->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('audio')->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('thumbnail')
                                        ->defaultValue('orchestra-media-thumbnail-audio.png')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('facades')->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('media')
                        ->defaultValue('OpenOrchestra\MediaAdminBundle\Facade\MediaFacade')
                     ->end()
                    ->scalarNode('media_collection')
                        ->defaultValue('OpenOrchestra\MediaAdminBundle\Facade\MediaCollectionFacade')
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
