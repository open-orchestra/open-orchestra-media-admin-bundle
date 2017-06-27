<?php

namespace OpenOrchestra\MediaAdminBundle\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApi\Transformer\AbstractTransformer;

/**
 * Class MediaTypeCollectionTransformer
 */
class MediaTypeCollectionTransformer extends AbstractTransformer
{
    /**
     * @param ArrayCollection $mixed
     * @param string|null     $folderId
     *
     * @return FacadeInterface
     */
    public function transform($mixed, $folderId = null)
    {
        $facade = $this->newFacade();

        foreach ($mixed as $media) {
            if ($media->getMediaType() != "") {
                $facade->addMediaType($this->getContext()->transform('media_type', $media, $folderId));
            }
        }

        return $facade;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_type_collection';
    }
}
