<?php

namespace OpenOrchestra\MediaAdmin\Perimeter\Strategy;

use OpenOrchestra\Backoffice\Model\PerimeterInterface;
use OpenOrchestra\ModelInterface\Model\ContentTypeInterface;
use OpenOrchestra\Backoffice\Perimeter\Strategy\PerimeterStrategyInterface;

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
        return ContentTypeInterface::ENTITY_TYPE;
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
        if (is_string($item)) {
            foreach ($perimeter->getItems() as $path) {
                if (0 === strpos($path, $item)) {
                    return true;
                }
            }
        }

        return false;
    }
}