<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OpenOrchestraMediaAdminExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('open_orchestra_media_admin.tmp_dir', $config['tmp_dir']);
        $container->setParameter(
            'open_orchestra_media_admin.max_width_generation',
            $config['max_width_generation']
        );
        $container->setParameter(
            'open_orchestra_media_admin.max_height_generation',
            $config['max_height_generation']
        );

        foreach ($config['facades'] as $transformer => $facade) {
            $container->setParameter('open_orchestra_media_admin.facade.' . $transformer . '.class', $facade);
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('transformer.yml');
        $loader->load('navigation_panel.yml');
        $loader->load('subscriber.yml');
        $loader->load('form.yml');
        $loader->load('manager.yml');
        $loader->load('twig.yml');
        $loader->load('generator.yml');
        $loader->load('display.yml');
        $loader->load('icon.yml');
        $loader->load('extract_reference.yml');
        $loader->load('file_utils.yml');
        $loader->load('mime_type.yml');
        $loader->load('file_alternatives.yml');
        $loader->load('voter.yml');
        $loader->load('role_parameter.yml');
        $loader->load('event.yml');

        $this->addMediaFieldType($container);
        $this->setFilesParameters($container, $config, $loader);
    }

    /**
     * Merge app conf with bundle conf
     *
     * @param ContainerBuilder $container
     */
    protected function addMediaFieldType(ContainerBuilder $container)
    {
        $fieldTypes = array_merge(
            $container->getParameter('open_orchestra_backoffice.field_types'),
            array(
                'orchestra_media' => array(
                    'label' => 'open_orchestra_media_admin.form.field_type.custom_type.media',
                    'type' => 'oo_media_choice',
                    'deserialize_type' => 'array',
                    'search' => 'text',
                    'default_value' => array(
                        'type' => 'oo_media_choice',
                        'options' => array(
                            'label' => 'open_orchestra_backoffice.form.field_type.default_value',
                            'required' => false,
                        ),
                    ),
                    'options' => array(
                        'required' => array(
                            'default_value' => false,
                        ),
                        'filter' => array(
                            'default_value' => ''
                        )
                    )
                )
            )
        );

        $container->setParameter('open_orchestra_backoffice.field_types', $fieldTypes);

        $options = array_merge(
            $container->getParameter('open_orchestra_backoffice.options'),
            array(
                'filter' => array(
                    'type' => 'choice',
                    'label' => 'open_orchestra_media_admin.form.field_type.field_option.filter',
                    'required' => false,
                    'choices' => array(
                        '' => 'open_orchestra_media_admin.media_filter.none',
                        'default' => 'open_orchestra_media_admin.media_filter.default',
                        'image' => 'open_orchestra_media_admin.media_filter.image',
                        'audio' => 'open_orchestra_media_admin.media_filter.audio',
                        'video' => 'open_orchestra_media_admin.media_filter.video',
                        'pdf' => 'open_orchestra_media_admin.media_filter.pdf',
                    ),
                )
            )
        );

        $container->setParameter('open_orchestra_backoffice.options', $options);
    }

    /**
     * Add the files generation parameters to the container
     * Takes the Open Orchestra alternatives image formats or the application one instead if defined
     *
     * @param ContainerBuilder $container
     * @param array            $config
     * @param YamlFileLoader   $loader
     */
    protected function setFilesParameters(ContainerBuilder $container, array $config, YamlFileLoader $loader)
    {
        $container->setParameter(
            'open_orchestra_media_admin.files.thumbnail_format',
            $config['thumbnail']
        );

        $container->setParameter(
            'open_orchestra_media_admin.files.alternatives.default.thumbnail',
            $config['files']['alternatives']['default']['thumbnail']
        );

        $container->setParameter(
            'open_orchestra_media_admin.files.alternatives.audio.thumbnail',
            $config['files']['alternatives']['audio']['thumbnail']
            );

        if (count($config['files']['alternatives']['image']['formats']) > 0) {
            $container->setParameter(
                'open_orchestra_media_admin.files.alternatives.image.formats',
                $config['files']['alternatives']['image']['formats']
            );
        } else {
            $loader->load('alternatives_formats.yml');
        }

    }
}
