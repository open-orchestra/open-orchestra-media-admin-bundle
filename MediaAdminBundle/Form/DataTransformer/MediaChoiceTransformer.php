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
            && isset($value['mediaId'])
            && strpos($value['mediaId'], MediaInterface::MEDIA_PREFIX) === 0
        ) {
            $value['mediaId'] = substr($value['mediaId'], strlen(MediaInterface::MEDIA_PREFIX));
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
            && isset($value['mediaId'])
            && strpos($value['mediaId'], MediaInterface::MEDIA_PREFIX) !== 0
        ) {
            $value['mediaId'] == MediaInterface::MEDIA_PREFIX . $value['mediaId'];
        }

        return $value;
    }
}
