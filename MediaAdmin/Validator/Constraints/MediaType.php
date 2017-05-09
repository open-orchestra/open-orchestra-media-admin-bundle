<?php

namespace OpenOrchestra\MediaAdmin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class MediaType
 */
class MediaType extends Constraint
{
    public $message = 'open_orchestra_media_admin_validators.media_type';
    public $filter;

    /**
     * @return string|void
     */
    public function validatedBy()
    {
        return 'media_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return array('filter');
    }

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
