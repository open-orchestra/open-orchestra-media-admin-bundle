<?php

namespace OpenOrchestra\MediaAdminBundle\Form\Type;

use FOS\UserBundle\Model\GroupableInterface;
use OpenOrchestra\Backoffice\Model\GroupInterface;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Symfony\Component\Form\FormBuilderInterface;
use OpenOrchestra\ModelInterface\Model\SiteInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use OpenOrchestra\MediaAdminBundle\Form\DataTransformer\EmbedSiteToSiteIdTransformer;

/**
 * Class SiteForFolderChoiceType
 */
class SiteForFolderChoiceType extends AbstractType
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
        $choices = array();

        $token = $this->tokenStorage->getToken();
        if ($token && ($user = $token->getUser()) instanceof GroupableInterface) {
            if ($user->isSuperAdmin()) {
                return $this->getChoicesAllSite();
            }
            $userGroups = $user->getGroups();
            /** @var GroupInterface $group */
            foreach ($userGroups as $group) {
                if ($group->hasRole(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER)) {
                    /** @var SiteInterface $site */
                    if ($site = $group->getSite()) {
                        if (false === $site->isDeleted() && ! isset($choices[$site->getSiteId()])) {
                            $choices[$site->getSiteId()] = $site->getName();
                        }
                    } else {
                        return $this->getChoicesAllSite();
                    }
                }
            }
        }

        return $choices;
    }

    /**
     * @return array
     */
    protected function getChoicesAllSite()
    {
        $choices = array();
        $sites = $this->siteRepository->findByDeleted(false);
        /** @var SiteInterface $site */
        foreach ($sites as $site) {
            $choices[$site->getSiteId()] = $site->getName();
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
        return 'oo_site_for_folder_choice';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }
}
