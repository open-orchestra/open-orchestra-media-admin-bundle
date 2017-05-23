<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraProductionFixturesInterface;

/**
 * Class LoadSiteData
 */
class LoadSiteData extends AbstractFixture implements OrderedFixtureInterface, OrchestraProductionFixturesInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $site2 = $this->getReference('site2');
        $site2->addBlock('slideshow');
        $site2->addBlock('gallery');

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 320;
    }
}
