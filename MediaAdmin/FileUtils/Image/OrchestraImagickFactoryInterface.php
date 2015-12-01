<?php

namespace OpenOrchestra\MediaAdmin\FileUtils\Image;

/**
 * Interface OrchestraImagickFactoryInterface
 */
interface OrchestraImagickFactoryInterface
{
    /**
     * @param mixed|null $files
     *
     * @return ImageManagerInterface
     */
    public function create($files = null);
}
