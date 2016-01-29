<?php

namespace OpenOrchestra\MediaAdminBundle\Facade;

use JMS\Serializer\Annotation as Serializer;
use OpenOrchestra\BaseApi\Facade\AbstractFacade;

/**
 * Class MediaFolderGroupRoleFacade
 */
class MediaFolderGroupRoleFacade extends AbstractFacade
{
    /**
     * @Serializer\Type("string")
     */
    public $name;

    /**
     * @Serializer\Type("string")
     */
    public $folder;

    /**
     * @Serializer\Type("string")
     */
    public $accessType;
}
