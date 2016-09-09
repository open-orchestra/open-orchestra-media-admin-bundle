<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives;

use OpenOrchestra\Backoffice\Exception\MissingFileAlternativesStrategyException;
use OpenOrchestra\Media\Model\MediaInterface;
use InvalidArgumentException;

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
     * Try to find the $media type
     *
     * @param MediaInterface $media
     *
     * @return string
     */
    public function getMediaType(MediaInterface $media) {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                return $strategy->getMediaType();
            }
        }

        return $this->defaultStrategy->getMediaType();
    }

    /**
     * Try to find a strategy to generate the thumbnail for $media and run it
     *
     * @param MediaInterface $media
     */
    public function generateThumbnail(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                $strategy->generateThumbnail($media);

                return;
            }
        }

        $this->defaultStrategy->generateThumbnail($media);
    }

    /**
     * Try to find a strategy to set media information
     *
     * @param MediaInterface $media
     */
    public function setMediaInformation(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                $strategy->setMediaInformation($media);

                return;
            }
        }

        $this->defaultStrategy->setMediaInformation($media);
    }

    /**
     * Try to find a strategy to generate the required file alternatives and run it
     *
     * @param MediaInterface $media
     */
    public function generateAlternatives(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                $strategy->generateAlternatives($media);

                return;
            }
        }

        $this->defaultStrategy->generateAlternatives($media);
    }

    /**
     * Try to find a strategy to delete the thumbnail for $media and run it
     *
     * @param MediaInterface $media
     */
    public function deleteThumbnail(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                $strategy->deleteThumbnail($media);

                return;
            }
        }

        $this->defaultStrategy->deleteThumbnail($media);
    }

    /**
     * Try to find a strategy to delete the file alternatives for $media and run it
     *
     * @param MediaInterface $media
     */
    public function deleteAlternatives(MediaInterface $media)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                $strategy->deleteAlternatives($media);

                return;
            }
        }

        $this->defaultStrategy->deleteAlternatives($media);
    }

    /**
     * Try to find a strategy to override the file alternative for $media and run it
     * 
     * @param MediaInterface $media
     * @param string         $newFilePath
     * @param string         $formatName
     */
    public function overrideAlternative(MediaInterface $media, $newFilePath, $formatName)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->support($media)) {
                $strategy->overrideAlternative($media, $newFilePath, $formatName);

                return;
            }
        }

        $this->defaultStrategy->overrideAlternative($media, $newFilePath, $formatName);
    }
}
