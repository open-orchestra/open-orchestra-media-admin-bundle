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
     * @param mixed $subEntity
     */
    public function addReferencesToEntity($entity, $subEntity)
    {
        if ($this->support($entity)) {
            $keywordIds = $this->extractKeywordsFromKeywordableEntity($entity);

            foreach ($keywordIds as $keywordId) {
                /** @var OpenOrchestra\ModelInterface\Model\KeywordInterface $keyword */
                $keyword = $this->keywordRepository->find($keywordId);
                if ($keyword) {
                    $keyword->addUseInEntity($entity->getId(), MediaInterface::ENTITY_TYPE);
                }
            }
        }
    }

    /**
     * @param mixed $entity
     */
    public function removeReferencesToEntity($entity)
    {
        if ($this->support($entity)) {
            $mediaId = $entity->getId();

            $keywordsUsedInMedia = $this->keywordRepository
                ->findByUsedInEntity($mediaId, MediaInterface::ENTITY_TYPE);

            foreach ($keywordsUsedInMedia as $keyword) {
                $keyword->removeUseInEntity($mediaId, MediaInterface::ENTITY_TYPE);
            }
        }
    }
}
