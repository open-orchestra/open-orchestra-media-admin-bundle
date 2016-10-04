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
        $rootImages->setName('Images folder');
        $rootImages->setSiteId($site->getSiteId());
        $manager->persist($rootImages);
        $this->addReference('mediaFolder-rootImages', $rootImages);

        $firstImages = new MediaFolder();
        $firstImages->setName('First images folder');
        $firstImages->setParent($rootImages);
        $firstImages->setSiteId($site->getSiteId());
        $manager->persist($firstImages);
        $this->addReference('mediaFolder-firstImages', $firstImages);

        $rootFiles = new MediaFolder();
        $rootFiles->setName('Files folder');
        $rootFiles->setSiteId($site->getSiteId());
        $manager->persist($rootFiles);
        $this->addReference('mediaFolder-rootFiles', $rootFiles);

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
