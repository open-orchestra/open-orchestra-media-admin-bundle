<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\MediaModelBundle\Document\MediaFolder;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;

/**
 * Class LoadFolderData
 */
class LoadFolderData extends AbstractFixture implements OrderedFixtureInterface, OrchestraFunctionalFixturesInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $site =  $this->getReference('site2');

        $rootImages = new MediaFolder();
        $rootImages->setName('Images');
        $rootImages->setSiteId($site->getSiteId());
        $manager->persist($rootImages);
        $this->addReference('mediaFolder-rootImages', $rootImages);

        $animatedImages = new MediaFolder();
        $animatedImages->setName('Animated Images');
        $animatedImages->setParent($rootImages);
        $animatedImages->setSiteId($site->getSiteId());
        $manager->persist($animatedImages);
        $this->addReference('mediaFolder-animatedImages', $animatedImages);

        $transparentImages = new MediaFolder();
        $transparentImages->setName('Transparent Images');
        $transparentImages->setParent($rootImages);
        $transparentImages->setSiteId($site->getSiteId());
        $manager->persist($transparentImages);
        $this->addReference('mediaFolder-transparentImages', $transparentImages);

        $gifImages = new MediaFolder();
        $gifImages->setName('Gif');
        $gifImages->setParent($animatedImages);
        $gifImages->setSiteId($site->getSiteId());
        $manager->persist($gifImages);
        $this->addReference('mediaFolder-gifImages', $gifImages);

        $rootFiles = new MediaFolder();
        $rootFiles->setName('Files');
        $rootFiles->setSiteId($site->getSiteId());
        $manager->persist($rootFiles);
        $this->addReference('mediaFolder-rootFiles', $rootFiles);

        $pdfFolder = new MediaFolder();
        $pdfFolder->setName('Pdf');
        $pdfFolder->setParent($rootFiles);
        $pdfFolder->setSiteId($site->getSiteId());
        $manager->persist($pdfFolder);
        $this->addReference('mediaFolder-pdfFolder', $pdfFolder);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 350;
    }

}
