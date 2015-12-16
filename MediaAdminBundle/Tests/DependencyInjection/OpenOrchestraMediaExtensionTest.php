<?php

namespace OpenOrchestra\MediaAdminBundle\DependencyInjection;

use OpenOrchestra\MediaAdminBundle\DependencyInjection\OpenOrchestraMediaAdminExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class OpenOrchestraMediaAdminExtensionTest
 */
class OpenOrchestraMediaAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @param string $tmp
     * @param int    $compression
     * @param array  $thumbnail
     * @param array  $facades
     *
     * @dataProvider provideConfig
     */
    public function testConfig($file, $tmp, $compression, array $thumbnail, array $facades)
    {
        $container = $this->loadContainerFromFile($file);

        $this->assertEquals($tmp, $container->getParameter('open_orchestra_media_admin.tmp_dir'));
        $this->assertEquals(
            $thumbnail,
            $container->getParameter('open_orchestra_media_admin.files.alternatives.image.formats')
        );
        foreach ($facades as $parameter => $facadeClass) {
            $this->assertEquals($facadeClass, $container->getParameter('open_orchestra_media_admin.facade.'.$parameter.'.class'));
        }
    }

    /**
     * @return array
     */
    public function provideConfig()
    {
        return array(
            array('empty', '/tmp', 75,
                array(
                    'fixed_height' => array('max_height' => 100, 'compression_quality' => 75),
                    'fixed_width' => array('max_width' => 100, 'compression_quality' => 75),
                    'rectangle' => array('max_width' => 100, 'max_height' => 70, 'compression_quality' => 75)
                ),
                array(
                    'media' => 'OpenOrchestra\MediaAdminBundle\Facade\MediaFacade',
                    'media_collection' => 'OpenOrchestra\MediaAdminBundle\Facade\MediaCollectionFacade',
                )
            ),
            array('value', 'fake_tmp', 10000,
                array(
                    'fixed_height' => array('max_height' => 5000, 'compression_quality' => 10000),
                    'fixed_width' => array('max_width' => 5000, 'compression_quality' => 10000),
                    'rectangle' => array('max_width' => 5000, 'max_height' => 5000, 'compression_quality' => 10000)
                ),
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
