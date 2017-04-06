<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Api;

use OpenOrchestra\BaseApiBundle\Controller\BaseController;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;

/**
 * Class MediaLibrarySharingController
 *
 * @Config\Route("media-library-sharing")
 *
 * @Api\Serialize()
 */
class MediaLibrarySharingController extends BaseController
{
    /**
     * @Config\Route("/list-sites", name="media_library_sharing_list_sites")
     * @Config\Method({"GET"})
     * @Config\Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return FacadeInterface
     */
    public function listSitesAction()
    {
        $contextManager = $this->get('open_orchestra_backoffice.context_manager');
        $currentSiteId = $contextManager->getCurrentSiteId();

        $siteIdsAllowedShare = array($currentSiteId);

        $mediaLibrariesSharing = $this->get('open_orchestra_media.repository.media_library_sharing')->findAllowedSites($currentSiteId);
        foreach ($mediaLibrariesSharing as $mediaLibrarySharing) {
            $siteIdsAllowedShare[] = $mediaLibrarySharing->getSiteId();
        }
        $siteAllowedShare = $this->get('open_orchestra_model.repository.site')->findBySiteIds($siteIdsAllowedShare);

        $collectionTransformer = $this->get('open_orchestra_api.transformer_manager')->get('site_collection');
        $facade = $collectionTransformer->transform($siteAllowedShare);

        return $facade;
    }
}
