<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Symfony\Component\Form\FormBuilderInterface;
use OpenOrchestra\ModelInterface\Model\SiteInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use OpenOrchestra\MediaAdminBundle\Form\DataTransformer\EmbedSiteToSiteIdTransformer;

/**
 * Class OrchestraSiteForFolderChoiceType
 */
class OrchestraSiteForFolderChoiceType extends AbstractType
{
    protected $siteRepository;
    protected $tokenStorage;
    protected $embedSiteToSiteTransformer;

    /**
     * @param SiteRepositoryInterface $siteRepository
     */
    public function __construct(
        SiteRepositoryInterface $siteRepository,
        TokenStorageInterface $tokenStorage,
        EmbedSiteToSiteIdTransformer $embedSiteToSiteIdTransformer
    )
    {
        $this->siteRepository = $siteRepository;
        $this->tokenStorage = $tokenStorage;
        $this->embedSiteToSiteIdTransformer = $embedSiteToSiteIdTransformer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['embed']) {
            $builder->addModelTransformer($this->embedSiteToSiteIdTransformer);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'embed' => false,
                'choices' => $this->getChoices()
            )
        );
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $sites = $this->siteRepository->findByDeleted(false);
        $choices = array();
        $availablesSites = array();

        /** @var SiteInterface $site */
        foreach ($sites as $site) {
            $availablesSites[$site->getSiteId()] = $site->getName();
        }

        $userGroups = $this->tokenStorage->getToken()->getUser()->getGroups();
        /** @var GroupInterface $group */
        foreach ($userGroups as $group) {

            if ($group->hasRole(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER)) {

                if ($group->getSite()) {

                    if (isset($availablesSites[$group->getSite()->getSiteId()])) {
                        $choices[$group->getSite()->getSiteId()] = $group->getSite()->getName();
                    }
                } else {

                    return $availablesSites;
                }
            }
        }

        return $choices;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'orchestra_site_for_folder_choice';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }
}
