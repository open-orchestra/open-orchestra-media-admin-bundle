<?php

namespace OpenOrchestra\MediaAdminBundle\Exceptions;

/**
 * Class MediaFolderGroupRoleNotFoundException
 */
class MediaFolderGroupRoleNotFoundException extends \Exception
{
    /**
     * @param string $role
     * @param string $folder
     * @param string $group
     */
    public function __construct($role, $folder, $group)
    {
        parent::__construct(
            sprintf('The role "%s" of media folder "%s" was not found in group "%s"', $role, $folder, $group)
        );
    }
}
