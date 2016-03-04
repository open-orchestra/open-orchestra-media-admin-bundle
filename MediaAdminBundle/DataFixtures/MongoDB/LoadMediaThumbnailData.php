<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadMediaThumbnailData
 */
class LoadMediaThumbnailData
    extends AbstractFixture
    implements ContainerAwareInterface, OrderedFixtureInterface, OrchestraFunctionalFixturesInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->deployThumbnail('orchestra-media-thumbnail-default.png');
        $this->deployThumbnail('orchestra-media-thumbnail-audio.png');
    }

    /**
     * Deploy a logo on the media storage
     * 
     * @param string $thumbnailName
     */
    protected function deployThumbnail($thumbnailName)
    {
        $mediaStorageManager = $this->container->get('open_orchestra_media_file.manager.storage');
        $fileDir = 'web/bundles/openorchestramediaadmin/images/';

        $mediaStorageManager->uploadFile($thumbnailName, $fileDir . DIRECTORY_SEPARATOR . $thumbnailName, false);
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 10;
    }
}
