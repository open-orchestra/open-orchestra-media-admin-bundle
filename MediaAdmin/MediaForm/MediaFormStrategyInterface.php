<?php

namespace OpenOrchestra\MediaAdmin\MediaForm;

use OpenOrchestra\Media\Model\MediaInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Interface MediaFormStrategyInterface
 */
interface MediaFormStrategyInterface
{
    /**
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function support(MediaInterface $media);

    /**
     * Get the form type associated with $media
     *
     * @return string
     */
    public function getFormType();

    /**
     * Run additional process when the form submission is valid
     *
     * @param MediaInterface $media
     * @param FormInterface  $form
     */
    public function runAdditionalProcess(MediaInterface $media, FormInterface $form);

    /**
     * @return string
     */
    public function getName();
}
