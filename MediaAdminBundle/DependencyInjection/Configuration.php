<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface;

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
            ->scalarNode('max_width_generation')->defaultValue(5000)->end()
            ->scalarNode('max_height_generation')->defaultValue(5000)->end()
            ->arrayNode('thumbnail')->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('max_width')->defaultValue(236)->end()
                    ->scalarNode('max_height')->defaultValue(97)->end()
                    ->scalarNode('compression_quality')->defaultValue(75)->end()
                ->end()
            ->end()
            ->arrayNode('media_type_filter')
                ->info('Media type available to filter')
                ->useAttributeAsKey('type')
                ->defaultValue(array(
                    'default' => 'open_orchestra_media_admin.media_filter.default',
                    'image' => 'open_orchestra_media_admin.media_filter.image',
                    'audio' => 'open_orchestra_media_admin.media_filter.audio',
                    'video' => 'open_orchestra_media_admin.media_filter.video',
                    'pdf' => 'open_orchestra_media_admin.media_filter.pdf',
                ))
                ->prototype('scalar')->end()
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
                    ->scalarNode('media_type')
                        ->defaultValue('OpenOrchestra\MediaAdminBundle\Facade\MediaTypeFacade')
                    ->end()
                    ->scalarNode('media_type_collection')
                        ->defaultValue('OpenOrchestra\MediaAdminBundle\Facade\MediaTypeCollectionFacade')
                    ->end()
                    ->scalarNode('folder')
                        ->defaultValue('OpenOrchestra\MediaAdminBundle\Facade\FolderFacade')
                    ->end()
                    ->scalarNode('folder_tree')
                        ->defaultValue('OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade')
                    ->end()
                ->end()
            ->end()
            ->append($this->addConfigurationRoleConfiguration())
        ->end();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function addConfigurationRoleConfiguration()
    {
        $builder = new TreeBuilder();
        $configurationRole = $builder->root('configuration_roles');

        $configurationRole
            ->info('Array configuration roles')
            ->prototype('array')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->end()
            ->end();

        $configurationRole->defaultValue(array(
            'open_orchestra_backoffice.role.contribution' => array(
                'firstpackage' => array(
                    'folder' => array(
                        ContributionRoleInterface::MEDIA_FOLDER_CONTRIBUTOR => array(
                            'label' => 'open_orchestra_backoffice.role.contributor.label',
                        ),
                        ContributionRoleInterface::MEDIA_FOLDER_SUPER_EDITOR => array(
                            'label' => 'open_orchestra_backoffice.role.editor.label',
                        ),
                        ContributionRoleInterface::MEDIA_FOLDER_SUPER_SUPRESSOR => array(
                            'label' => 'open_orchestra_backoffice.role.supressor.label',
                        ),
                    ),
                    'media' => array(
                        ContributionRoleInterface::MEDIA_CONTRIBUTOR => array(
                            'label' => 'open_orchestra_backoffice.role.contributor.label',
                        ),
                        ContributionRoleInterface::MEDIA_SUPER_EDITOR => array(
                            'label' => 'open_orchestra_backoffice.role.editor.label',
                        ),
                        ContributionRoleInterface::MEDIA_SUPER_SUPRESSOR => array(
                            'label' => 'open_orchestra_backoffice.role.supressor.label',
                        ),
                    ),
                ),
            ),
        ));

        return $configurationRole;
    }
}
