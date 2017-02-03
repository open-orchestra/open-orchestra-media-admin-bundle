<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface;

/**
 * Class OpenOrchestraMediaAdminExtensionTest
 */
class OpenOrchestraMediaAdminExtensionTest extends AbstractBaseTestCase
{
    /**
     * @param string $file
     * @param string $tmpDir
     * @param array  $alternatives
     * @param array  $thumbnail
     * @param string $default
     * @param string $audio
     * @param array  $facades
     *
     * @dataProvider provideConfig
     */
    public function testConfig(
        $file,
        $tmpDir,
        array $alternatives,
        array $thumbnail,
        $default,
        $audio,
        array $facades
    ) {
        $container = $this->loadContainerFromFile($file);

        $this->assertEquals($tmpDir, $container->getParameter('open_orchestra_media_admin.tmp_dir'));
        $this->assertEquals(
            $alternatives,
            $container->getParameter('open_orchestra_media_admin.files.alternatives.image.formats')
        );

        foreach ($facades as $parameter => $facadeClass) {
            $this->assertEquals($facadeClass, $container->getParameter('open_orchestra_media_admin.facade.'.$parameter.'.class'));
        }

        $this->assertEquals(
            $thumbnail,
            $container->getParameter('open_orchestra_media_admin.files.thumbnail_format')
        );
        $this->assertEquals(
            $default,
            $container->getParameter('open_orchestra_media_admin.files.alternatives.default.thumbnail')
        );
        $this->assertEquals(
            $audio,
            $container->getParameter('open_orchestra_media_admin.files.alternatives.audio.thumbnail')
        );

        $configurationRoles = array(
            'firstpackage' => array(
                'folder' => array(
                    ContributionRoleInterface::MEDIA_FOLDER_CONTRIBUTOR => 'open_orchestra_backoffice.role.contributor',
                    ContributionRoleInterface::MEDIA_FOLDER_SUPER_EDITOR => 'open_orchestra_backoffice.role.editor',
                    ContributionRoleInterface::MEDIA_FOLDER_SUPER_SUPRESSOR => 'open_orchestra_backoffice.role.suppresor',
                ),
                'media' => array(
                    ContributionRoleInterface::MEDIA_CONTRIBUTOR => 'open_orchestra_backoffice.role.contributor',
                    ContributionRoleInterface::MEDIA_SUPER_EDITOR => 'open_orchestra_backoffice.role.editor',
                    ContributionRoleInterface::MEDIA_SUPER_SUPRESSOR => 'open_orchestra_backoffice.role.suppresor',
                ),
            ),
        );

        $this->assertEquals($configurationRoles, $container->getParameter('open_orchestra_backoffice.configuration.roles'));
    }

    /**
     * @return array
     */
    public function provideConfig()
    {
        return array(
            array(
                'empty',
                '/tmp',
                array(
                    'fixed_height' => array('max_height' => 100, 'compression_quality' => 75),
                    'fixed_width' => array('max_width' => 100, 'compression_quality' => 75),
                    'rectangle' => array('max_width' => 100, 'max_height' => 70, 'compression_quality' => 75)
                ),
                array('max_height' => 97, 'max_width' => 236, 'compression_quality' => 75),
                'orchestra-media-thumbnail-default.png',
                'orchestra-media-thumbnail-audio.png',
                array(
                    'media' => 'OpenOrchestra\MediaAdminBundle\Facade\MediaFacade',
                    'media_collection' => 'OpenOrchestra\MediaAdminBundle\Facade\MediaCollectionFacade',
                    'folder' => 'OpenOrchestra\MediaAdminBundle\Facade\FolderFacade',
                    'folder_tree' => 'OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade',
                )
            ),
            array(
                'value',
                'fake_tmp',
                array(
                    'fixed_height' => array('max_height' => 5000, 'compression_quality' => 10000),
                    'fixed_width' => array('max_width' => 5000, 'compression_quality' => 10000),
                    'rectangle' => array('max_width' => 5000, 'max_height' => 5000, 'compression_quality' => 10000)
                ),
                array('max_height' => 5000, 'max_width' => 5000, 'compression_quality' => 10000),
                'default.png',
                'audio.png',
                array(
                    'media' => 'FacadeClass',
                    'media_collection' => 'FacadeClass',
                )
            )
        );
    }

    /**
     * @param string $file
     *
     * @return ContainerBuilder
     */
    private function loadContainerFromFile($file)
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.cache_dir', '/tmp');
        $container->setParameter('kernel.bundles', array());
        $container->setParameter('open_orchestra_backoffice.field_types', array());
        $container->setParameter('open_orchestra_backoffice.options', array());
        $container->registerExtension(new OpenOrchestraMediaAdminExtension());

        $locator = new FileLocator(__DIR__ . '/Fixtures/config/');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load($file . '.yml');
        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
