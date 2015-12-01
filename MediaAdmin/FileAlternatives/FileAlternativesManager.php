<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class FileAlternativesManager
 */

class FileAlternativesManager
{
    protected $strategies = array();

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

        return $media;
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

        return $media;
    }
}
