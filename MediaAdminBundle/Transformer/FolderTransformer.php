<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use OpenOrchestra\BaseApi\Exceptions\TransformerParameterTypeException;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\MediaAdminBundle\Facade\FolderFacade;
use OpenOrchestra\Media\Model\MediaFolderInterface;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\Media\Repository\FolderRepositoryInterface;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OpenOrchestra\MediaAdmin\Event\FolderEventFactory;
use OpenOrchestra\ModelInterface\Manager\MultiLanguagesChoiceManagerInterface;

/**
 * Class FolderTransformer
 */
class FolderTransformer extends AbstractTransformer
{
    protected $folderRepository;
    protected $eventDispatcher;
    protected $folderEventFactory;

    /**
     * @param string                               $facadeClass
     * @param FolderRepositoryInterface            $folderRepository,
     * @param EventDispatcherInterface             $eventDispatcher
     * @param FolderEventFactory                   $folderEventFactory
     * @param MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
     */
    public function __construct(
        $facadeClass = null,
        FolderRepositoryInterface $folderRepository,
        EventDispatcherInterface $eventDispatcher,
        FolderEventFactory $folderEventFactory,
        MultiLanguagesChoiceManagerInterface $multiLanguageChoiceManager
    ) {
        parent::__construct($facadeClass);
        $this->folderRepository = $folderRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->folderEventFactory = $folderEventFactory;
        $this->multiLanguageChoiceManager = $multiLanguageChoiceManager;
    }

    /**
     * @param FolderInterface $folder
     *
     * @return FolderFacade
     *
     * @throws TransformerParameterTypeException
     */
    public function transform($folder)
    {
        if (!$folder instanceof FolderInterface) {
            throw new TransformerParameterTypeException();
        }
        $facade = $this->newFacade();
        $facade->folderId = $folder->getId();
        $facade->name = $this->multiLanguageChoiceManager->choose($mixed->getNames());
        $facade->createdAt = $folder->getCreatedAt();
        $facade->updatedAt = $folder->getUpdatedAt();
        if ($folder->getParent() instanceof FolderInterface) {
            $facade->parentId = $folder->getParent()->getId();
        } else {
            $facade->parentId = FolderInterface::ROOT_PARENT_ID;
        }
        $facade->siteId = $folder->getSiteId();

        return $facade;
    }

    /**
     * @param FacadeInterface $facade
     * @param mixed|null      $source
     *
     * @return mixed
     */
    public function reverseTransform(FacadeInterface $facade, $source = null)
    {
        if ($source instanceof MediaFolderInterface) {
            $parent = $this->folderRepository->findOneById($facade->parentId);
            $source->setParent($parent);
            $oldPath = $source->getPath();
            $source->setPath($parent->getPath() . '/' . $source->getFolderId());
            $event = $this->folderEventFactory->createFolderEvent();
            $event->setFolder($source);
            $event->setPreviousPath($oldPath);
            $this->eventDispatcher->dispatch(FolderEvents::PATH_UPDATED, $event);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'folder';
    }
}
