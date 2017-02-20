<?php

namespace OpenOrchestra\MediaAdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class MediaChoiceTransformer
 */
class MediaChoiceTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($value)
    {
        return $this->formatValue($value);
    }

    /**
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($value)
    {
        return $this->formatValue($value);
    }

    /**
     * Format $value as a media array if the value is initialized
     *
     * @param array|string $value
     *
     * @return array
     */
    protected function formatValue($value)
    {
        if ($value == '') {
            $value = array('id' => '', 'format' => '', 'alt' => '', 'legend' => '');
        }

        return $value;
    }
}
