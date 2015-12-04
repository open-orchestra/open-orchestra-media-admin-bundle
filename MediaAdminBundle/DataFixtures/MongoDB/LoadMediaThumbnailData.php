<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraProductionFixturesInterface;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;

/**
 * Class LoadMediaThumbnailData
 */
class LoadMediaThumbnailData
    extends AbstractFixture
    implements ContainerAwareInterface, OrderedFixtureInterface, OrchestraProductionFixturesInterface
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
     * @param string $logoName
     */
    protected function deployThumbnail($thumbnailName)
    {
        $uploadMediaManager = $this->container->get('open_orchestra_media_file.manager.uploaded_media');
        $fileDir = 'web/bundles/openorchestramediaadmin/images/';

        $uploadMediaManager->uploadContent($thumbnailName, file_get_contents($fileDir . DIRECTORY_SEPARATOR . $thumbnailName));
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
