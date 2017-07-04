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
     * @param array           $params
     *
     * @return FacadeInterface
     */
    public function transform($mixed, array $params = array())
    {
        $facade = $this->newFacade();

        foreach ($mixed as $media) {
            if ($media->getMediaType() != "") {
                $facade->addMediaType($this->getContext()->transform('media_type', $media));
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
