<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\DependencyInjection\Compiler;

use OpenOrchestra\MediaAdminBundle\DependencyInjection\Compiler\ClientConfigurationCompilerPass;
use Phake;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test ClientConfigurationCompilerPassTest
 */
class ClientConfigurationCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientConfigurationCompilerPass
     */
    protected $compiler;
    protected $containerBuilder;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->containerBuilder = Phake::mock(ContainerBuilder::CLASS);

        $this->compiler = new ClientConfigurationCompilerPass();
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf(CompilerPassInterface::CLASS, $this->compiler);
    }

    /**
     * Test process
     */
    public function testProcess()
    {
        $mediaFilterType = array();

        $definition = Phake::mock(Definition::CLASS);
        Phake::when($this->containerBuilder)->getDefinition(Phake::anyParameters())->thenReturn($definition);
        Phake::when($this->containerBuilder)->getParameter('open_orchestra_media_admin.media_type_filter')->thenReturn($mediaFilterType);

        $this->compiler->process($this->containerBuilder);
        Phake::verify($definition)->addMethodCall('addClientConfiguration', array('media_filter_type', $mediaFilterType));
    }
}
