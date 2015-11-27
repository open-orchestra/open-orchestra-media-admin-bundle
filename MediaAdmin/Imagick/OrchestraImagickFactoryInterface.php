<?php

namespace OpenOrchestra\MediaAdmin\Imagick;

/**
 * Interface OrchestraImagickFactoryInterface
 */
interface OrchestraImagickFactoryInterface
{
    /**
     * @param mixed|null $files
     *
     * @return OrchestraImagickInterface
     */
    public function create($files = null);
}
