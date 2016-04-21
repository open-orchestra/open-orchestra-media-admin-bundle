<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use OpenOrchestra\ModelInterface\DataFixtures\OrchestraFunctionalFixturesInterface;

/**
 * Class LoadGroupFunctionalData
 */
class LoadGroupFunctionalData extends AbstractLoadGroupData implements OrchestraFunctionalFixturesInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $groupFoldersCreate = $this->generateGroup(
            'Media folders create',
            'Media folders create',
            'Groupe de dossiers de la médiathèque ayant les droits access et create',
            'site2',
            'group-folders-create',
            TreeFolderPanelStrategy::ROLE_ACCESS_MEDIA_FOLDER
        );
        $groupFoldersCreate->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER);
        $manager->persist($groupFoldersCreate);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 602;
    }
}
