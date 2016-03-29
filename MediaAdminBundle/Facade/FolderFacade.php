<?php

namespace OpenOrchestra\MediaAdminBundle\Facade;

use OpenOrchestra\BaseApi\Facade\AbstractFacade;
use JMS\Serializer\Annotation as Serializer;
use OpenOrchestra\BaseApi\Facade\Traits\TimestampableFacade;

/**
 * Class FolderFacade
 */
class FolderFacade extends AbstractFacade
{
    use TimestampableFacade;

    /**
     * @Serializer\Type("string")
     */
    public $folderId;

    /**
     * @Serializer\Type("string")
     */
    public $name;

    /**
     * @Serializer\Type("string")
     */
    public $type;

    /**
     * @Serializer\Type("string")
     */
    public $parentId;

    /**
     * @Serializer\Type("string")
     */
    protected $siteId = "";

    /**
     * @param string $siteId
     */
    public function addSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * @return string
     */
    public function getSiteId()
    {
        return $this->siteId;
    }
}
