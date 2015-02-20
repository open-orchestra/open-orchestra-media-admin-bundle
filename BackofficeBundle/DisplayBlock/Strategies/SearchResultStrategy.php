<?php

namespace PHPOrchestra\BackofficeBundle\DisplayBlock\Strategies;

use PHPOrchestra\DisplayBundle\DisplayBlock\DisplayBlockInterface;
use PHPOrchestra\DisplayBundle\DisplayBlock\Strategies\AbstractStrategy;
use PHPOrchestra\ModelInterface\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchResultStrategy
 */
class SearchResultStrategy extends AbstractStrategy
{
    /**
     * Check if the strategy support this block
     *
     * @param BlockInterface $block
     *
     * @return boolean
     */
    public function support(BlockInterface $block)
    {
        return DisplayBlockInterface::SEARCH_RESULT == $block->getComponent();
    }

    /**
     * Perform the show action for a block
     *
     * @param BlockInterface $block
     *
     * @return Response
     */
    public function show(BlockInterface $block)
    {
        return $this->render(
            'PHPOrchestraBackofficeBundle:Block/SearchResult:show.html.twig',
            array(
                'nodeId' => $block->getAttribute('nodeId'),
                'nbdoc' => $block->getAttribute('nbdoc'),
                'fielddisplayed' => implode(', ', $block->getAttribute('fielddisplayed')),
                'nbfacet' => count($block->getAttribute('facets')),
                'nbfilter' => count($block->getAttribute('filter'))
            )
        );
    }

    /**
     * Get the name of the strategy
     *
     * @return string
     */
    public function getName()
    {
        return 'search_result';
    }
}
