<?php

namespace OpenOrchestra\MediaAdminBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use OpenOrchestra\GroupBundle\Document\Group;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;

/**
 * Class AbstractLoadGroupData
 */
abstract class AbstractLoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param string $name
     * @param string $enLabel
     * @param string $frLabel
     * @param string $siteNumber
     * @param string $referenceName
     * @param string $role
     *
     * @return Group
     */
    protected function generateGroup($name, $enLabel, $frLabel, $siteNumber, $referenceName, $role = null)
    {
        $group = new Group();
        $group->setName($name);

        $group->addLabel('en', $enLabel);
        $group->addLabel('fr', $frLabel);

        if (is_null($role)) {
            $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_MEDIA_FOLDER);
            $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER);
            $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER);
            $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA_FOLDER);
            $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA);
            $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA);
            $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA);
        } else {
            $group->addRole($role);
        }

        $group->setSite($this->getReference($siteNumber));
        $this->setReference($referenceName, $group);

        return $group;
    }

    /**
     * @param string $groupName
     */
    protected function addRole($groupName)
    {
        $group = $this->getReference($groupName);
        $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_MEDIA_FOLDER);
        $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER);
        $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER);
        $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA_FOLDER);
        $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA);
        $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA);
        $group->addRole(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA);
    }
}
