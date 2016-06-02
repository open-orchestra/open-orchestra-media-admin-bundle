<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraProductionFixturesInterface;

/**
 * Class LoadGroupData
 */
class LoadGroupData extends AbstractLoadGroupData implements OrchestraProductionFixturesInterface, OrchestraFunctionalFixturesInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $groupFolders = $this->generateGroup(
            'Media folders group',
            'Media folders group',
            'Groupe de dossiers de la médiathèque',
            'site2',
            'group-folders'
        );
        $manager->persist($groupFolders);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 601;
    }
}
