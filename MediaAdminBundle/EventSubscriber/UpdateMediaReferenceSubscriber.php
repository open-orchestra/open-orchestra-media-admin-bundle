<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\ModelInterface\Event\StatusableEvent;
use OpenOrchestra\ModelInterface\Event\TrashcanEvent;
use OpenOrchestra\ModelInterface\StatusEvents;
use OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceManager;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use OpenOrchestra\ModelInterface\TrashcanEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\Event\NodeEvent;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
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
     * @param StatusableEvent $event
     */
    public function updateMediaReference(StatusableEvent $event)
    {
        $statusableElement = $event->getStatusableElement();
        $references = $this->extractReferenceManager->extractReference($statusableElement);

        $methodToCall = 'removeUsageReference';
        if ($statusableElement->getStatus()->isPublished()) {
            $methodToCall = 'addUsageReference';
        }

        $this->updateReferences($references, $methodToCall);
    }

    /**
     * @param NodeEvent $event
     */
    public function updateMediaReferenceForTransverserNode(NodeEvent $event)
    {
        $node = $event->getNode();

        $this->removeReferencesToNode($node);

        if ($node->getNodeType() === NodeInterface::TYPE_TRANSVERSE) {
            $references = $this->extractReferenceManager->extractReference($node);
            $this->updateReferences($references);
        }
    }

    /**
     * @param TrashcanEvent $event
     */
    public function removeEntity(TrashcanEvent $event)
    {
        $deletedElement = $event->getDeletedEntity();
        if ($deletedElement instanceof StatusableInterface) {
            $references = $this->extractReferenceManager->extractReference($deletedElement);

            $this->updateReferences($references, 'removeUsageReference');
        }
    }

    /**
     * Update Media References
     *
     * @param array  $references
     * @param string $mode
     */
    protected function updateReferences(array $references, $mode = 'addUsageReference')
    {
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
     * @param StatusableInterface $node
     *
     * @throws \OpenOrchestra\MediaAdminBundle\Exceptions\ExtractReferenceStrategyNotFound
     */
    protected function removeReferencesToNode(StatusableInterface $node)
    {
        $nodePattern = $this->extractReferenceManager->getReferencePattern($node);

        $mediasReferencingNode = $this->mediaRepository->findByUsagePattern($nodePattern);

        foreach ($mediasReferencingNode as $media) {
            $references = $media->getUsageReference();
            foreach ($references as $reference) {
                if (strpos($reference, $nodePattern) === 0) {
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
            StatusEvents::STATUS_CHANGE => 'updateMediaReference',
            NodeEvents::NODE_UPDATE_BLOCK => 'updateMediaReferenceForTransverserNode',
            NodeEvents::NODE_DELETE_BLOCK => 'updateMediaReferenceForTransverserNode',
            NodeEvents::NODE_UPDATE_BLOCK_POSITION => 'updateMediaReferenceForTransverserNode',
            TrashcanEvents::TRASHCAN_DELETE_ENTITY => 'removeEntity',
        );
    }
}
