<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type\Component;

use Doctrine\ODM\MongoDB\DocumentRepository;
use OpenOrchestra\Backoffice\Context\ContextBackOfficeInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionActionInterface as MediaContributionActionInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class FolderChoiceType
 */
class FolderChoiceType extends AbstractType
{
    protected $currentSiteManager;
    protected $authorizationChecker;
    protected $folderClass;

    /**
     * @param ContextBackOfficeInterface    $currentSiteManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $folderClass
     */
    public function __construct(
        ContextBackOfficeInterface $currentSiteManager,
        AuthorizationCheckerInterface $authorizationChecker,
        $folderClass
    ) {
        $this->currentSiteManager = $currentSiteManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->folderClass = $folderClass;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'class'         => $this->folderClass,
                'property'      => 'names[' . $this->currentSiteManager->getBackOfficeLanguage() . ']',
                'site_id'       => $this->currentSiteManager->getSiteId(),
                'query_builder' => function (Options $options) {
                    return function(DocumentRepository $documentRepository) use ($options) {
                        return $documentRepository->createQueryBuilder()
                            ->field('parent')->exists(false)
                            ->field('siteId')->equals($options['site_id']);
                    };
                },
                'attr' => array(
                    'class' => 'orchestra-tree-choice',
                )
            )
        );
    }

    public function buildView(FormView $view, FormInterface $form ,array $options)
    {
        $folders = [];
        foreach ($view->vars['choices'] as $folder) {
            $folders[] = $folder->data;
        }
        $folders = $this->buildTreeFolders($folders);
        $view->vars['choices'] = $folders;
    }

    protected function buildTreeFolders(array $folders, $depth = 0)
    {
        $lastFolder = end($folders);
        foreach ($folders as $folder) {
            $result[] = new ChoiceView(
                $folder,
                $folder->getId(),
                $folder->getName($this->currentSiteManager->getBackOfficeLanguage()),
                array(
                    'disabled' => !$this->authorizationChecker->isGranted(MediaContributionActionInterface::CREATE_MEDIA_UNDER, $folder),
                    'data-depth' => $depth,
                    'data-last' => $folder === $lastFolder,
                )
            );
            if (!$folder->getSubFolders()->isEmpty()) {
                $result = array_merge(
                    $result,
                    $this->buildTreeFolders($folder->getSubFolders()->toArray(), $depth + 1)
                );
            }
        }

        return $result;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'oo_folder_choice';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'document';
    }
}
