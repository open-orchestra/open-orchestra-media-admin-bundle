<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\Media\Model\MediaLibrarySharingInterface;
use OpenOrchestra\Media\Repository\MediaLibrarySharingRepositoryInterface;
use OpenOrchestra\ModelInterface\Model\SiteInterface;
use OpenOrchestra\ModelInterface\Repository\SiteRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class MediaLibrarySharingSubscriber
 */
class MediaLibrarySharingSubscriber implements EventSubscriberInterface
{
    protected $mediaLibrarySharingRepository;
    protected $mediaLibrarySharingClass;
    protected $objectManager;
    protected $siteRepository;

    /**
     * @param MediaLibrarySharingRepositoryInterface $mediaLibrarySharingRepository
     * @param String                                 $mediaLibrarySharingClass
     * @param ObjectManager                          $objectManager
     * @param SiteRepositoryInterface                $siteRepository
     */
    public function __construct(
        MediaLibrarySharingRepositoryInterface $mediaLibrarySharingRepository,
        $mediaLibrarySharingClass,
        ObjectManager $objectManager,
        SiteRepositoryInterface $siteRepository
    ) {
        $this->mediaLibrarySharingRepository = $mediaLibrarySharingRepository;
        $this->mediaLibrarySharingClass = $mediaLibrarySharingClass;
        $this->objectManager = $objectManager;
        $this->siteRepository = $siteRepository;
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT => 'postSubmit',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        if ($event->getData() instanceof SiteInterface) {
            $currentSiteId = $event->getData()->getSiteId();
            $choices = $this->getChoices($currentSiteId);

            if (count($choices) > 0 ) {
                $siteAllowedShare = array();

                $mediaLibrarySharing = $this->mediaLibrarySharingRepository->findOneBySiteId($currentSiteId);
                if ($mediaLibrarySharing instanceof MediaLibrarySharingInterface) {
                    $siteAllowedShare = $mediaLibrarySharing->getAllowedSites();
                }

                $event->getForm()->add('media_sharing', 'oo_site_choice', array(
                    'multiple' => true,
                    'expanded' => true,
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                    'choices' => $choices,
                    'group_id' => 'content',
                    'sub_group_id' => 'media',
                    'data' => $siteAllowedShare
                ));
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        if ($event->getForm()->isValid() && $event->getForm()->has('media_sharing')) {
            $siteId = $event->getData()->getSiteId();
            $mediaLibrarySharing = $this->mediaLibrarySharingRepository->findOneBySiteId($siteId);
            if (!$mediaLibrarySharing instanceof MediaLibrarySharingInterface) {
                $mediaLibrarySharing = new $this->mediaLibrarySharingClass();
                $mediaLibrarySharing->setSiteId($siteId);
            }
            $mediaLibrarySharing->setAllowedSites($event->getForm()->get('media_sharing')->getData());
            $this->objectManager->persist($mediaLibrarySharing);
            $this->objectManager->flush();
        }
    }

    /**
     * @param string $currentSiteId
     *
     * @return array
     */
    protected function getChoices($currentSiteId = null)
    {
        $sites = $this->siteRepository->findByDeleted(false);
        $choices = array();

        foreach ($sites as $site) {
            if (null === $currentSiteId || $currentSiteId !== $site->getSiteId()) {
                $choices[$site->getName()] = $site->getSiteId();
            }
        }

        return $choices;
    }
}
