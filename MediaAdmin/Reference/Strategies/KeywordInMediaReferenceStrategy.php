<?php

namespace OpenOrchestra\MediaAdmin\Reference\Strategies;

use OpenOrchestra\Backoffice\Reference\Strategies\ReferenceStrategyInterface;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\ModelInterface\Repository\KeywordRepositoryInterface;
use OpenOrchestra\Backoffice\Reference\Strategies\AbstractKeywordReferenceStrategy;

/**
 * Class KeywordInMediaReferenceStrategy
 */
class KeywordInMediaReferenceStrategy extends AbstractKeywordReferenceStrategy implements ReferenceStrategyInterface
{
    protected $keywordRepository;

    /**
     * @param KeywordRepositoryInterface $keywordRepository
     */
    public function __construct(KeywordRepositoryInterface $keywordRepository)
    {
        $this->keywordRepository = $keywordRepository;
    }

    /**
     * @param mixed $entity
     *
     * @return boolean
     */
    public function support($entity)
    {
        return ($entity instanceof MediaInterface);
    }

    /**
     * @param mixed $entity
     */
    public function addReferencesToEntity($entity)
    {
        $keywordIds = $this->extractKeywordsFromKeywordableEntity($entity);

        foreach ($keywordIds as $keywordId) {
            /** @var KeywordInterface $keyword */
            $keyword = $this->keywordRepository->find($keywordId);
            $keyword->addUseInMedia($entity->getId());
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeReferencesToEntity($entity)
    {
        $mediaId = $entity->getId();

        $keywordsUsedInMedia = $this->keywordRepository
            ->findByUsedInEntity($mediaId, MediaInterface::ENTITY_TYPE);

        foreach ($keywordsUsedInMedia as $keyword) {
            $keyword->removeUseInMedia($mediaId);
        }
    }
}
