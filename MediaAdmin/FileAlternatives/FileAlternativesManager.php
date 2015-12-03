<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class FileAlternativesManager
 */

class FileAlternativesManager
{
    protected $strategies = array();
    protected $defaultStrategy;

    /**
     * @param FileAlternativesStrategyInterface $defaultStrategy
     */
    public function __construct(FileAlternativesStrategyInterface $defaultStrategy)
    {
        $this->defaultStrategy = $defaultStrategy;
    }

    /**
     * Add $strategy to the manager
     *
     * @param FileAlternativesStrategyInterface $strategy
     */
    public function addStrategy(FileAlternativesStrategyInterface $strategy)
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    /**
     * Try to find a strategy to generate the thumbnail for $media and run it
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateThumbnail(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                return $strategy->generateThumbnail($media);
            }
        }

        return $this->defaultStrategy->generateThumbnail($media);
    }

    /**
     * Try to find a strategy to generate the required file alternatives and run it
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function generateAlternatives(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                return $strategy->generateAlternatives($media);
            }
        }

        return $this->defaultStrategy->generateAlternatives($media);
    }

    /**
     * Try to find a strategy to delete the thumbnail for $media and run it
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function deleteThumbnail(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                return $strategy->deleteThumbnail($media);
            }
        }

        return $this->defaultStrategy->deleteThumbnail($media);
    }

    /**
     * Try to find a strategy to delete the file alternatives for $media and run it
     *
     * @param MediaInterface $media
     *
     * @return MediaInterface
     */
    public function deleteAlternatives(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                return $strategy->deleteAlternatives($media);
            }
        }

        return $this->defaultStrategy->deleteAlternatives($media);
    }
}
