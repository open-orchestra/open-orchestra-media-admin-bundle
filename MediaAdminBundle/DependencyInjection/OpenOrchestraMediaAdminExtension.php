<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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
            'open_orchestra_media_admin.files.thumbnail_format',
            $config['thumbnail']
        );

        $container->setParameter(
            'open_orchestra_media_admin.files.alternatives.image.formats',
            $config['alternatives']['image']['formats']
        );

        $container->setParameter(
            'open_orchestra_media_admin.files.alternatives.default.thumbnail',
            $config['alternatives']['default']['thumbnail']
        );

        $container->setParameter(
            'open_orchestra_media_admin.files.alternatives.audio.thumbnail',
            $config['alternatives']['audio']['thumbnail']
        );

        foreach ($config['facades'] as $transformer => $facade) {
            $container->setParameter('open_orchestra_media_admin.facade.' . $transformer .'.class', $facade);
        }

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('transformer.yml');
        $loader->load('navigation_panel.yml');
        $loader->load('subscriber.yml');
        $loader->load('listener.yml');
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

        $this->addMediaFieldType($container);
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
                    )
                )
            )
        );

        $container->setParameter('open_orchestra_backoffice.field_types', $fieldTypes);
    }
}
