<?php

namespace OpenOrchestra\MediaAdmin\MediaForm;

use OpenOrchestra\Media\Model\MediaInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class MediaFormManager
 */
class MediaFormManager
{
    protected $strategies = array();
    protected $defaultStrategy;

    /**
     * @param MediaFormStrategyInterface $defaultStrategy
     */
    public function __construct(MediaFormStrategyInterface $defaultStrategy)
    {
        $this->defaultStrategy = $defaultStrategy;
    }

    /**
     * Add $strategy to the manager
     *
     * @param MediaFormStrategyInterface $strategy
     */
    public function addStrategy(MediaFormStrategyInterface $strategy)
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    /**
     * Try to find the $media type
     *
     * @param MediaInterface $media
     *
     * @return string
     */
    public function getFormType(MediaInterface $media) {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                return $strategy->getFormType();
            }
        }

        return $this->defaultStrategy->getFormType();
    }

    /**
     * Run additional process when the form submission is valid
     *
     * @param MediaInterface $media
     * @param FormInterface  $form
     */
    public function runAdditionalProcess(MediaInterface $media, FormInterface $form)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                return $strategy->runAdditionalProcess($media, $form);
            }
        }

        return $this->defaultStrategy->runAdditionalProcess($media, $form);
    }
}
