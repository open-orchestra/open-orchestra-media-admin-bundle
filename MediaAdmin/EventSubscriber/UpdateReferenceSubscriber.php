<?php

namespace OpenOrchestra\MediaAdmin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\Backoffice\Reference\ReferenceManager;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;

/**
 * Class UpdateReferenceSubscriber
 */
class UpdateReferenceSubscriber implements EventSubscriberInterface
{
    protected $referenceManager;
    protected $objectManager;

    /**
     * @param ReferenceManager $referenceManager
     * @param ObjectManager    $objectManager
     */
    public function __construct(ReferenceManager $referenceManager, ObjectManager $objectManager)
    {
        $this->referenceManager = $referenceManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param MediaEvent $event
     */
    public function updateReferencesToMedia(MediaEvent $event)
    {
        $media = $event->getMedia();
        $this->referenceManager->updateReferencesToEntity($media);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            MediaEvents::MEDIA_UPDATE => 'updateReferencesToMedia',
        );
    }
}
