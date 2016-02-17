<?php

namespace OpenOrchestra\MediaAdminBundle\DisplayIcon\Strategies;

use OpenOrchestra\Backoffice\DisplayIcon\Strategies\AbstractStrategy;
use OpenOrchestra\Media\DisplayBlock\Strategies\MediaListByKeywordStrategy as BaseMediaListByKeywordStrategy;

/**
 * Class MediaListIconStrategy
 */
class MediaListStrategy extends AbstractStrategy
{
    /**
     * Check if the strategy support this block
     *
     * @param string $block
     *
     * @return boolean
     */
    public function support($block)
    {
        return BaseMediaListByKeywordStrategy::NAME == $block;
    }

    /**
     * Display an icon for a block
     *
     * @return string
     */
    public function show()
    {
        return $this->render('OpenOrchestraMediaAdminBundle:Block/MediaList:showIcon.html.twig');
    }

    /**
     * Get the name of the strategy
     *
     * @return string
     */
    public function getName()
    {
        return 'media_list';
    }
}
