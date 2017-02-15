<?php

namespace OpenOrchestra\MediaAdminBundle\GeneratePerimeter\Strategy;

use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use OpenOrchestra\Backoffice\GeneratePerimeter\Strategy\GeneratePerimeterStrategyInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\Backoffice\GeneratePerimeter\Strategy\GeneratePerimeterStrategy;

/**
 * Class FolderGeneratePerimeterStrategy
 */
class FolderGeneratePerimeterStrategy extends GeneratePerimeterStrategy implements GeneratePerimeterStrategyInterface
{
    protected $folderRepository;

    /**
     * @param FolderRepositoryInterface $folderRepository
     * @param CurrentSiteIdInterface    $contextManager
     */
    public function __construct(
        FolderRepositoryInterface $folderRepository,
        CurrentSiteIdInterface $contextManager
    ) {
        $this->folderRepository = $folderRepository;
        parent::__construct($contextManager);
    }

    /**
     * Return the supported perimeter type
     *
     * @return string
     */
    public function getType()
    {
        return MediaFolderInterface::ENTITY_TYPE;
    }

    /**
     * Generate perimeter
     *
     * @return array
     */
    public function generatePerimeter()
    {
        $treeFolders = $this->folderRepository->findFolderTree($this->contextManager->getCurrentSiteId());

        return $this->generateTreePerimeter($treeFolders);
    }

    /**
     * get perimeter configuration
     *
     * @return array
     */
    public function getPerimeterConfiguration()
    {
        $treeFolders = $this->folderRepository->findFolderTree($this->contextManager->getCurrentSiteId());

        return $this->getTreePerimeterConfiguration($treeFolders);
    }

    /**
     * format perimeter
     *
     * @param array $treeFolders
     *
     * @return array
     */
    protected function formatPerimeter(array $treeFolders)
    {
        $path = array_key_exists('path', $treeFolders['folder']) ? $treeFolders['folder']['path'] : '';
        $treeFolders[] = $path;
        unset($treeFolders['folder']);
        foreach ($treeFolders['children'] as &$child) {
            $treeFolders = array_merge($treeFolders, $this->formatPerimeter($child));
        }
        unset($treeFolders['children']);

        return $treeFolders;
    }

    /**
     * format configuration
     *
     * @param array $treeFolders
     *
     * @return array
     */
    protected function formatConfiguration(array $treeFolders)
    {
        $treeFolders['root'] = array('path' => $treeFolders['folder']['path'], 'name' => $treeFolders['folder']['name']);
        unset($treeFolders['folder']);
        if (count($treeFolders['children']) == 0) {
            unset($treeFolders['children']);
        } else {
            $children = $treeFolders['children'];
            unset($treeFolders['children']);
            foreach ($children as $child) {
                $treeFolders['children'][] = $this->formatConfiguration($child);
            }
        }

        return $treeFolders;
    }
}
