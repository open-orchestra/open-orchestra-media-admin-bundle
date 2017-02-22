<?php

namespace OpenOrchestra\MediaAdminBundle\Facade;

use JMS\Serializer\Annotation as Serializer;
use OpenOrchestra\BaseApi\Facade\AbstractFacade;
use OpenOrchestra\BaseApi\Facade\Traits\BlameableFacade;
use OpenOrchestra\BaseApi\Facade\Traits\TimestampableFacade;

/**
 * Class MediaFacade
 */
class MediaFacade extends AbstractFacade
{
    use BlameableFacade;
    use TimestampableFacade;

    /**
     * @Serializer\Type("string")
     */
    public $name;

    /**
     * @Serializer\Type("string")
     */
    public $publicLink;

    /**
     * @Serializer\Type("string")
     */
    public $mimeType;

    /**
     * @Serializer\Type("string")
     */
    public $original;

    /**
     * @Serializer\Type("string")
     */
    public $thumbnail;

    /**
     * @Serializer\Type("string")
     */
    public $title;

    /**
     * @Serializer\Type("boolean")
     */
    public $isEditable;

    /**
     * @Serializer\Type("string")
     */
    public $mediaType;

    /**
     * @Serializer\Type("array<string, string>")
     */
    protected $alternatives = array();

    /**
     * @Serializer\Type("array<string, string>")
     */
    protected $mediaInformations = array();

    /**
     * @Serializer\Type("array<string>")
     */
    protected $keywords = array();

    /**
     * @param string $key
     * @param string $link
     */
    public function addAlternative($key, $link)
    {
        $this->alternatives[$key] = $link;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function addMediaInformation($key, $value)
    {
        $this->mediaInformations[$key] = $value;
    }

    /**
     * @param string $keyword
     */
    public function addKeyword($keyword)
    {
        $this->keywords[] = $keyword;
    }
}
