<?php

namespace OpenOrchestra\MediaAdminBundle\BusinessRules\Strategies;

use OpenOrchestra\Backoffice\BusinessRules\Strategies\AbstractBusinessRulesStrategy;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;

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
            ContributionActionInterface::DELETE => 'canDelete',
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
