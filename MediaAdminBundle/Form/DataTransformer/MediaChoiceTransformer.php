<?php

namespace OpenOrchestra\MediaAdminBundle\Form\DataTransformer;

use OpenOrchestra\Media\Model\MediaInterface;
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
        if (is_array($value)
            && isset($value['id'])
            && strpos($value['id'], MediaInterface::MEDIA_PREFIX) === 0
        ) {
            $value['id'] = substr($value['id'], strlen(MediaInterface::MEDIA_PREFIX));
        }

        if ($value == '') {
            $value = array('id' => '', 'format' => '');
        }

        return $value;
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
        if (
            is_array($value)
            && isset($value['id'])
            && strpos($value['id'], MediaInterface::MEDIA_PREFIX) !== 0
        ) {
            $value['id'] = MediaInterface::MEDIA_PREFIX . $value['id'];
        }

        if ($value == '') {
            $value = array('id' => '', 'format' => '');
        }

        return $value;
    }
}
