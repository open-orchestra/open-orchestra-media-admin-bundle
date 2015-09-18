<?php

namespace OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies;

use OpenOrchestra\Backoffice\NavigationPanel\Strategies\AbstractNavigationPanelStrategy;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;

/**
 * Class TreeFolderPanel
 */
class TreeFolderPanelStrategy extends AbstractNavigationPanelStrategy
{
    const ROLE_ACCESS_TREE_FOLDER = 'ROLE_ACCESS_TREE_FOLDER';

    /**
     * @var FolderRepositoryInterface
     */
    protected $folderRepository;

    /**
     * @var CurrentSiteIdInterface
     */
    protected $currentSiteManager;

    /**
     * @param FolderRepositoryInterface $folderRepository
     * @param CurrentSiteIdInterface    $currentSiteManager
     * @param string                    $parent
     * @param int                       $weight
     */
    public function __construct(FolderRepositoryInterface $folderRepository, CurrentSiteIdInterface $currentSiteManager, $parent, $weight)
    {
        parent::__construct('folders', self::ROLE_ACCESS_TREE_FOLDER, $weight, $parent);
        $this->folderRepository = $folderRepository;
        $this->currentSiteManager = $currentSiteManager;
    }

    /**
     * @return string
     */
    public function show($template = 'OpenOrchestraMediaAdminBundle:Tree:showFolderTree.html.twig', $parentId = null, $isAjax = false)
    {
        $siteId = $this->currentSiteManager->getCurrentSiteId();
        $folders = $this->folderRepository->findAllFolderBySiteAndParent($siteId, $parentId);
        foreach ($folders as $folder) {
            $folder->removeSubFolders();
        }

        return $this->render( $template, array(
            'folders' => $folders,
            'ajax' => $isAjax,
        ));
    }
}
