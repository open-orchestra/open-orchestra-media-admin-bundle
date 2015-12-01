<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Interface FileAlternativesStrategyInterface
 */
interface FileAlternativesStrategyInterface
{
    public function generateThumbnail(MediaInterface $media);

    public function generateAlternatives(MediaInterface $media);
}
