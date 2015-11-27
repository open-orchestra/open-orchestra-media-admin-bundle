<?php

namespace OpenOrchestra\MediaAdmin\Imagick;

/**
 * Class OrchestraImagickFactory
 */
class OrchestraImagickFactory implements OrchestraImagickFactoryInterface
{
    /**
     * @param mixed|null $files
     *
     * @return OrchestraImagickInterface
     */
    public function create($files = null)
    {
        return new OrchestraImagick($files);
    }
}
