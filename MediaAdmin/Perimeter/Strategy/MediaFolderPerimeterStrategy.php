<?php

namespace OpenOrchestra\MediaAdmin\Perimeter\Strategy;

use OpenOrchestra\Backoffice\Model\PerimeterInterface;
use OpenOrchestra\Backoffice\Perimeter\Strategy\PerimeterStrategyInterface;
use OpenOrchestra\Media\Model\MediaFolderInterface;

/**
 * Class MediaFolderPerimeterStrategy
 */
class MediaFolderPerimeterStrategy implements PerimeterStrategyInterface
{
    /**
     * Return the supported perimeter type
     */
    public function getType()
    {
        return MediaFolderInterface::ENTITY_TYPE;
    }

    /**
     * Check if $item is contained in $perimeter
     *
     * @param string $item
     *
     * @return boolean
     */
    public function isInPerimeter($item, PerimeterInterface $perimeter)
    {
        if ($perimeter->getType() == $this->getType() && is_string($item)) {
            foreach ($perimeter->getItems() as $path) {
                if (0 === strpos($item, $path)) {
                    return true;
                }
            }
        }

        return false;
    }
}
