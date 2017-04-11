<?php

namespace OpenOrchestra\MediaAdminBundle\BusinessRules\Strategies;

use OpenOrchestra\Backoffice\BusinessRules\Strategies\AbstractBusinessRulesStrategy;
use OpenOrchestra\Backoffice\BusinessRules\Strategies\BusinessActionInterface;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * class MediaStrategy
 */
class MediaStrategy extends AbstractBusinessRulesStrategy
{
    /**
     * @return string
     */
    public function getType()
    {
        return MediaInterface::ENTITY_TYPE;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return array(
            BusinessActionInterface::DELETE => 'canDelete',
        );
    }

    /**
     * @param MediaInterface $media
     * @param array          $parameters
     *
     * @return boolean
     */
    public function canDelete(MediaInterface $media, array $parameters)
    {
        return !$media->isUsed();
    }
}
