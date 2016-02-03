<?php

namespace OpenOrchestra\MediaAdminBundle\EventSubscriber;

use OpenOrchestra\ModelInterface\Event\StatusableEvent;
use OpenOrchestra\ModelInterface\StatusEvents;
use OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceManager;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use OpenOrchestra\Media\Helper\MediaWithFormatExtractorInterface;

/**
 * Class UpdateMediaReferenceSubscriber
 */
class UpdateMediaReferenceSubscriber implements EventSubscriberInterface
{
    protected $extractReferenceManager;
    protected $mediaRepository;
    protected $mediaFormatExtractor;

    /**
     * @param ExtractReferenceManager           $extractReferenceManager
     * @param MediaRepositoryInterface          $mediaRepository
     * @param MediaWithFormatExtractorInterface $mediaFormatExtractor
     */
    public function __construct(
        ExtractReferenceManager $extractReferenceManager,
        MediaRepositoryInterface $mediaRepository,
        MediaWithFormatExtractorInterface $mediaFormatExtractor
    ) {
        $this->extractReferenceManager = $extractReferenceManager;
        $this->mediaRepository = $mediaRepository;
        $this->mediaFormatExtractor = $mediaFormatExtractor;
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

        foreach ($references as $mediaWithFormat => $mediaUsage) {
            $mediaInfo = $this->mediaFormatExtractor->extractInformation($mediaWithFormat);
            /** @var MediaInterface $media */
            $media = $this->mediaRepository->find($mediaInfo['id']);
            foreach ($mediaUsage as $usage) {
                $media->$methodToCall($usage);
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
        );
    }
}
