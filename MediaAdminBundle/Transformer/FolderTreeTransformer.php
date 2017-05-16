<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\MediaAdminBundle\Facade\FolderTreeFacade;
use OpenOrchestra\BaseApi\Exceptions\HttpException\FacadeClassNotSetException;
use OpenOrchestra\BaseApi\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractSecurityCheckerAwareTransformer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionActionInterface as MediaContributionActionInterface;
use OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface;

/**
 * Class FolderTreeTransformer
 */
class FolderTreeTransformer extends AbstractSecurityCheckerAwareTransformer
{
    protected $folderFacadeClass;

    /**
     * @param string                        $treeFacadeClass
     * @param string                        $folderFacadeClass
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
     */
    public function __construct(
        $treeFacadeClass,
        $folderFacadeClass,
        AuthorizationCheckerInterface $authorizationChecker,
        MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
    ) {
        parent::__construct($treeFacadeClass, $authorizationChecker);
        $this->folderFacadeClass = $folderFacadeClass;
        $this->multiLanguageChoiceManager = $multiLanguageChoiceManager;
    }

    /**
     * @param array $folderCollection
     *
     * @return FolderTreeFacade
     */
    public function transform($folderCollection)
    {
        $facade = $this->newFacade();
        $facade->folder = null;

        if (is_array($folderCollection)) {
            foreach ($folderCollection as $folder) {
                $facade->addChild($this->transformSubTree($folder));
            }
        }

        return $facade;
    }

    /**
     * Transform the subtree having $folder as root
     *
     * @param array $folder
     *
     * @return FacadeInterface
     */
    protected function transformSubTree(array $folder)
    {
        $rootFolder = $folder['folder'];

        $folderFacade = $this->newFolderFacade();
        $folderFacade->id = (string)$rootFolder['_id'];
        $folderFacade->folderId = $rootFolder['folderId'];
        $folderFacade->name = $this->multiLanguageChoiceManager->choose($rootFolder['names']);
        $folderFacade->type = $rootFolder['type'];
        $folderFacade->siteId = $rootFolder['siteId'];
        $folderFacade->addRight(
            'can_edit',
            $this->authorizationChecker->isGranted(ContributionActionInterface::EDIT, $rootFolder)
        );
        $folderFacade->addRight(
            'can_create',
            $this->authorizationChecker->isGranted(ContributionActionInterface::CREATE, $rootFolder)
        );
        $folderFacade->addRight(
            'can_create_media',
            $this->authorizationChecker->isGranted(MediaContributionActionInterface::CREATE_MEDIA_UNDER, $rootFolder)
        );

        $treeFacade = $this->newFacade();
        $treeFacade->folder = $folderFacade;

        if (isset($folder['children']) && is_array($folder['children'])) {
            foreach ($folder['children'] as $children) {
                $treeFacade->addChild($this->transformSubTree($children));
            }
        }

        return $treeFacade;
    }

    /**
     * @return mixed
     *
     * @throws FacadeClassNotSetException
     * @throws TransformerParameterTypeException
     */
    protected function newFolderFacade()
    {
        if (null === $this->folderFacadeClass) {
            throw new FacadeClassNotSetException();
        }

        $facade = new $this->folderFacadeClass();

        if (!$facade instanceof FacadeInterface) {
            throw new TransformerParameterTypeException();
        }

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'folder_tree';
    }
}
