<?php

namespace OpenOrchestra\MediaAdminBundle\GeneratePerimeter\Strategy;

use OpenOrchestra\Backoffice\GeneratePerimeter\Strategy\GeneratePerimeterStrategyInterface;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\Backoffice\GeneratePerimeter\Strategy\GeneratePerimeterStrategy;
use OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface;

/**
 * Class FolderGeneratePerimeterStrategy
 */
class FolderGeneratePerimeterStrategy extends GeneratePerimeterStrategy implements GeneratePerimeterStrategyInterface
{
    protected $folderRepository;
    protected $multiLanguageChoiceManager;

    /**
     * @param FolderRepositoryInterface            $folderRepository
     * @param MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
     */
    public function __construct(
        FolderRepositoryInterface $folderRepository,
        MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
    ) {
        $this->folderRepository = $folderRepository;
        $this->multiLanguageChoiceManager = $multiLanguageChoiceManager;
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
     * @param string $siteId
     * @return array
     */
    public function generatePerimeter($siteId)
    {
        $treeFolders = $this->folderRepository->findFolderTree($siteId);

        return $this->generateTreePerimeter($treeFolders);
    }

    /**
     * get perimeter configuration
     *
     * @param string $siteId
     * @return array
     */
    public function getPerimeterConfiguration($siteId)
    {
        $treeFolders = $this->folderRepository->findFolderTree($siteId);

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
        $name = $this->multiLanguageChoiceManager->choose($treeFolders['folder']['names']);
        $treeFolders['root'] = array('path' => $treeFolders['folder']['path'], 'name' => $name);
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
