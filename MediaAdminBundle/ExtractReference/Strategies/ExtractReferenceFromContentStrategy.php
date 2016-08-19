<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies;

use OpenOrchestra\BaseBundle\Manager\TagManager;
use OpenOrchestra\BBcodeBundle\Parser\BBcodeParserInterface;
use OpenOrchestra\ModelInterface\Model\ContentAttributeInterface;
use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;
use OpenOrchestra\ModelInterface\Repository\ContentRepositoryInterface;

/**
 * Class ExtractReferenceFromContentStrategy
 */
class ExtractReferenceFromContentStrategy extends AbstractExtractReferenceStrategy
{
    const REFERENCE_PREFIX = 'content-';

    protected $contentRepository;

    /**
     * @param BBcodeParserInterface      $bbCoderParser
     * @param TagManager                 $tagManager
     * @param ContentRepositoryInterface $contentRepository
     */
    public function __construct(BBcodeParserInterface $bbCoderParser, TagManager $tagManager, ContentRepositoryInterface $contentRepository)
    {
        parent::__construct($bbCoderParser, $tagManager);
        $this->contentRepository = $contentRepository;
    }

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return bool
     */
    public function support(StatusableInterface $statusableElement)
    {
        return $statusableElement instanceof ContentInterface;
    }

    /**
     * @param string $reference
     *
     * @return bool
     */
    public function supportReference($reference)
    {
        return strpos($reference, self::REFERENCE_PREFIX) === 0;
    }

    /**
     * @param StatusableInterface|ContentInterface $statusableElement
     *
     * @return array
     */
    public function extractReference(StatusableInterface $statusableElement)
    {
        $references = array();

        /** @var ContentAttributeInterface $attribute */
        foreach ($statusableElement->getAttributes() as $key => $attribute) {
            $references = $this->extractMedia($key, $attribute->getValue(), $statusableElement->getId(), $references);
        }

        return $references;
    }

    /**
     * Get Reference pattern for $statusableElementId
     *
     * @param string $statusableElementId
     *
     * @return string
     */
    public function getReferencePattern($statusableElementId)
    {
        return self::REFERENCE_PREFIX . $statusableElementId;
    }

    /**
     * Get cache tag of statusable element for reference
     *
     * @param string $reference
     *
     * @return string|null
     */
    public function getStatusableElementCacheTag($reference)
    {
        $id = preg_replace('/^'. self::REFERENCE_PREFIX .'/', '', $reference);
        $content = $this->contentRepository->findById($id);

        if (null !== $content) {
            return $this->tagManager->formatContentIdTag($content->getContentId());
        }

        return null;
    }

    /**
     * Format a reference
     *
     * @param string $index
     * @param string $statusableElementId
     *
     * @return string
     */
    protected function formatReference($index, $statusableElementId)
    {
        return $this->getReferencePattern($statusableElementId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'content';
    }
}
