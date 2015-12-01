<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

use Imagick;

/**
 * Class ImagickFactory
 */
class ImagickFactory
{
    /**
     * @param mixed|null $files
     *
     * @return ImageManagerOldInterface
     */
    public function create($files = null)
    {
        return new Imagick($files);
    }
}
