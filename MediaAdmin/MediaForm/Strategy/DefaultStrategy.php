<?php

namespace OpenOrchestra\MediaAdmin\MediaForm\Strategy;

use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaAdmin\MediaForm\MediaFormStrategyInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class DefaultStrategy
 */
class DefaultStrategy implements MediaFormStrategyInterface
{
    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media)
    {
        return true;
    }

    /**
     * Get the form type associated tothe strategy $media
     *
     * @return string
     */
    public function getFormType()
    {
        return 'oo_media_base';
    }

    /**
     * Run additional process when the form submission is valid
     *
     * @param MediaInterface $media
     * @param FormInterface  $form
     */
    public function runAdditionalProcess(MediaInterface $media, FormInterface $form)
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'default_media_form_strategy';
    }
}
