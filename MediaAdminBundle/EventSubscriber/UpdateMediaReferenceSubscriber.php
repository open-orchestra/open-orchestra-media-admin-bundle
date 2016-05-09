<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\ModelInterface\ContentEvents;
use OpenOrchestra\ModelInterface\Event\ContentEvent;
use OpenOrchestra\ModelInterface\Event\TrashcanEvent;
use OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceManager;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use OpenOrchestra\ModelInterface\TrashcanEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\Event\NodeEvent;
use Doctrine\Common\Persistence\ObjectManager;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;

/**
 * Class UpdateMediaReferenceSubscriber
 */
class UpdateMediaReferenceSubscriber implements EventSubscriberInterface
{
    protected $extractReferenceManager;
    protected $mediaRepository;
    protected $objectManager;

    /**
     * @param ExtractReferenceManager  $extractReferenceManager
     * @param MediaRepositoryInterface $mediaRepository
     * @param ObjectManager            $objectManager
     */
    public function __construct(
        ExtractReferenceManager $extractReferenceManager,
        MediaRepositoryInterface $mediaRepository,
        ObjectManager $objectManager
    ) {
        $this->extractReferenceManager = $extractReferenceManager;
        $this->mediaRepository = $mediaRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param NodeEvent $event
     */
    public function updateMediaReferencesFromNode(NodeEvent $event)
    {
        $node = $event->getNode();
        $this->updateMediaReference($node);
    }


    /**
     * @param ContentEvent $event
     */
    public function updateMediaReferencesFromContent(ContentEvent $event)
    {
        $content = $event->getContent();
        $this->updateMediaReference($content);
    }

    /**
     * @param TrashcanEvent $event
     */
    public function removeEntity(TrashcanEvent $event)
    {
        $deletedElement = $event->getDeletedEntity();
        $this->updateReferences($deletedElement, 'removeUsageReference');
    }

    /**
     * @param StatusableInterface $statusableElement
     */
    protected function updateMediaReference(StatusableInterface $statusableElement)
    {
        $this->removeReferences($statusableElement);
        $this->updateReferences($statusableElement);
    }

    /**
     * Update Media References
     *
     * @param StatusableInterface $statusableElement
     * @param string              $mode
     */
    protected function updateReferences(StatusableInterface $statusableElement, $mode = 'addUsageReference')
    {
        $references = $this->extractReferenceManager->extractReference($statusableElement);
        foreach ($references as $mediaId => $mediaUsage) {
            /** @var MediaInterface $media */
            $media = $this->mediaRepository->find($mediaId);
            foreach ($mediaUsage as $usage) {
                $media->$mode($usage);
                $this->objectManager->persist($media);
            }
        }

        $this->objectManager->flush();
    }

    /**
     * @param StatusableInterface $statusableElement
     *
     * @throws \OpenOrchestra\MediaAdminBundle\Exceptions\ExtractReferenceStrategyNotFound
     */
    protected function removeReferences(StatusableInterface $statusableElement)
    {
        $elementPattern = $this->extractReferenceManager->getReferencePattern($statusableElement);

        $mediasReferencingNode = $this->mediaRepository->findByUsagePattern($elementPattern);

        foreach ($mediasReferencingNode as $media) {
            $references = $media->getUsageReference();
            foreach ($references as $reference) {
                if (strpos($reference, $elementPattern) === 0) {
                    $media->removeUsageReference($reference);
                    $this->objectManager->persist($media);
                }
            }
        }
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            NodeEvents::NODE_UPDATE_BLOCK => 'updateMediaReferencesFromNode',
            NodeEvents::NODE_DELETE_BLOCK => 'updateMediaReferencesFromNode',
            NodeEvents::NODE_UPDATE_BLOCK_POSITION => 'updateMediaReferencesFromNode',
            ContentEvents::CONTENT_UPDATE => 'updateMediaReferencesFromContent',
            ContentEvents::CONTENT_CREATION => 'updateMediaReferencesFromContent',
            TrashcanEvents::TRASHCAN_REMOVE_ENTITY => 'removeEntity',
        );
    }
}
