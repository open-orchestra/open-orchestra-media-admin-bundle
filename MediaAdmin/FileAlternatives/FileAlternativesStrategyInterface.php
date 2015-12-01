<?php

namespace OpenOrchestra\MediaAdmin\FileAlternatives;

use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Interface FileAlternativesStrategyInterface
 */
interface FileAlternativesStrategyInterface
{
     const THUMBNAIL_PREFIX = 'thumbnail';

     public function generateThumbnail(MediaInterface $media);

    public function generateAlternatives(MediaInterface $media);
}
