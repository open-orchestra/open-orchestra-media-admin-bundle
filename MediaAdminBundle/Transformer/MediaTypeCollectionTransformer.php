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
                $facade->addMediaType($this->getTransformer('media_type')->transform($media, $folderId));
            }
        }

        $facade->addLink('_no_filter', $this->generateRoute('open_orchestra_api_media_list', array(
            'folderId' => $folderId
        )));

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
