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
    const ROLE_ACCESS_MEDIA_FOLDER = 'ROLE_ACCESS_MEDIA_FOLDER';
    const ROLE_ACCESS_CREATE_MEDIA_FOLDER = 'ROLE_ACCESS_CREATE_MEDIA_FOLDER';
    const ROLE_ACCESS_UPDATE_MEDIA_FOLDER = 'ROLE_ACCESS_UPDATE_MEDIA_FOLDER';
    const ROLE_ACCESS_DELETE_MEDIA_FOLDER = 'ROLE_ACCESS_DELETE_MEDIA_FOLDER';
    const ROLE_ACCESS_CREATE_MEDIA = 'ROLE_ACCESS_CREATE_MEDIA';
    const ROLE_ACCESS_UPDATE_MEDIA = 'ROLE_ACCESS_UPDATE_MEDIA';
    const ROLE_ACCESS_DELETE_MEDIA = 'ROLE_ACCESS_DELETE_MEDIA';

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
    public function __construct(
        FolderRepositoryInterface $folderRepository,
        CurrentSiteIdInterface $currentSiteManager,
        $parent,
        $weight
    ){
        parent::__construct('folders', self::ROLE_ACCESS_MEDIA_FOLDER, $weight, $parent);
        $this->folderRepository = $folderRepository;
        $this->currentSiteManager = $currentSiteManager;
    }

    /**
     * @return string
     */
    public function show()
    {
        $siteId = $this->currentSiteManager->getCurrentSiteId();
        $rootFolders = $this->folderRepository->findAllRootFolderBySiteId($siteId);

       return $this->render( 'OpenOrchestraMediaAdminBundle:Tree:showFolderTree.html.twig', array(
           'folders' => $rootFolders,
        ));
    }
}
