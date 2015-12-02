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
            array('max_width' => '117', 'max_height' => '117', 'compression_quality' => '75')
        );

        $alternativesImages = $config['alternatives']['image'];
        $container->setParameter(
            'open_orchestra_media_admin.files.alternatives.image.formats',
            $alternativesImages
        );

        $loader = new Loader\YamlFileLoader(
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
