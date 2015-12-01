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
     * @return Imagick
     */
    public function create($files = null)
    {
        return new Imagick($files);
    }
}
