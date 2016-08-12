<?php

namespace OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies;

use OpenOrchestra\BaseBundle\Manager\TagManager;
use OpenOrchestra\BBcodeBundle\Parser\BBcodeParserInterface;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\ModelInterface\Model\StatusableInterface;
use OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface;

/**
 * Class ExtractReferenceFromNodeStrategy
 */
class ExtractReferenceFromNodeStrategy extends AbstractExtractReferenceStrategy
{
    const REFERENCE_PREFIX = 'node-';

    protected $nodeRepository;

    /**
     * @param BBcodeParserInterface   $bbCoderParser
     * @param TagManager              $tagManager
     * @param NodeRepositoryInterface $nodeRepository
     */
    public function __construct(BBcodeParserInterface $bbCoderParser, TagManager $tagManager, NodeRepositoryInterface $nodeRepository)
    {
        parent::__construct($bbCoderParser, $tagManager);
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param StatusableInterface $statusableElement
     *
     * @return bool
     */
    public function support(StatusableInterface $statusableElement)
    {
        return $statusableElement instanceof NodeInterface;
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
     * @param StatusableInterface|NodeInterface $statusableElement
     *
     * @return array
     */
    public function extractReference(StatusableInterface $statusableElement)
    {
        $references = array();

        /** @var BlockInterface $block */
        foreach ($statusableElement->getBlocks() as $key => $block) {
            $references = $this->extractMedia($key, $block->getAttributes(), $statusableElement->getId(), $references);
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
        return self::REFERENCE_PREFIX . $statusableElementId . '-';
    }

    /**
     * Get invalidate tag of statusable element for reference
     *
     * @param string $reference
     *
     * @return string|null
     */
    public function getInvalidateTagStatusableElement($reference)
    {
        $id = preg_replace('/^'. self::REFERENCE_PREFIX .'/', '', $reference);
        $id = preg_replace('/-[0-9]*$/', '', $id);
        $node = $this->nodeRepository->findVersionByDocumentId($id);
        if (null !== $node) {
            return $this->tagManager->formatNodeIdTag($node->getNodeId());
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
        return $this->getReferencePattern($statusableElementId) . $index;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'node';
    }
}
