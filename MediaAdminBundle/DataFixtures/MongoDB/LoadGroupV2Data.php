<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\GroupBundle\DataFixtures\MongoDB\AbstractLoadGroupV2Data;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class LoadGroupV2Data
 */
class LoadGroupV2Data extends AbstractLoadGroupV2Data implements OrchestraFunctionalFixturesInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $group = $this->getReference('group2');

        $mediaPerimeter = $this->createPerimeter(MediaInterface::ENTITY_TYPE, array('first_images_folder'));
        $mediaProfileCollection = $this->createProfileCollection(array('profile-Contributor', 'profile-Validator'));
        $group->addWorkflowProfileCollection(MediaInterface::ENTITY_TYPE, $mediaProfileCollection);
        $group->addPerimeter($mediaPerimeter);

        $manager->persist($group);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 622;
    }
}
