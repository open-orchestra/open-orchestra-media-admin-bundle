<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
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
            ->arrayNode('alternatives')->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('image')->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('formats')->addDefaultsIfNotSet()
                                ->children()
                                    ->arrayNode('fixed_height')->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('max_height')->defaultValue(100)->end()
                                            ->scalarNode('compression_quality')->defaultValue(75)->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('fixed_width')->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('max_width')->defaultValue(100)->end()
                                            ->scalarNode('compression_quality')->defaultValue(75)->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('rectangle')->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('max_width')->defaultValue(100)->end()
                                            ->scalarNode('max_height')->defaultValue(70)->end()
                                            ->scalarNode('compression_quality')->defaultValue(75)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('default')->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('thumbnail')->defaultValue('orchestra-media-thumbnail-default.png')->end()
                        ->end()
                    ->end()
                    ->arrayNode('audio')->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('thumbnail')->defaultValue('orchestra-media-thumbnail-audio.png')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
