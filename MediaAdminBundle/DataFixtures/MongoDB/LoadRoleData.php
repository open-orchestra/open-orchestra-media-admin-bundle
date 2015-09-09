<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\BackofficeBundle\DataFixtures\MongoDB\AbstractLoadRoleData;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;

/**
 * Class LoadRoleData
 */
class LoadRoleData extends AbstractLoadRoleData implements OrchestraFunctionalFixturesInterface
{
    /**
     * @param ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $manager->persist($this->generateRole(TreeFolderPanelStrategy::ROLE_ACCESS_TREE_FOLDER));

        $manager->flush();
    }
}
