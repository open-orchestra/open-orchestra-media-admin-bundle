<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

/**
 * Class OrchestraImagickFactory
 */
class OrchestraImagickFactory implements OrchestraImagickFactoryInterface
{
    /**
     * @param mixed|null $files
     *
     * @return ImageManagerInterface
     */
    public function create($files = null)
    {
        return new OrchestraImagick($files);
    }
}
