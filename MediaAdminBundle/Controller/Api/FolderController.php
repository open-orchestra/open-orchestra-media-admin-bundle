<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Api;

use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\BaseApiBundle\Controller\BaseController;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\MediaAdminBundle\Exceptions\HttpException\FolderNotDeletableException;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FolderController
 *
 * @Config\Route("folder")
 *
 * @Api\Serialize()
 */
class FolderController extends BaseController
{
    /**
     * @param string $folderId
     *
     * @Config\Route("/{folderId}/delete", name="open_orchestra_api_folder_delete")
     * @Config\Method({"DELETE"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_DELETE_MEDIA_FOLDER')")
     *
     * @throws FolderNotDeletableException
     *
     * @return Response
     */
    public function deleteAction($folderId)
    {
        $folder = $this->get('open_orchestra_media.repository.media_folder')->find($folderId);
        if ($folder) {
            $this->denyAccessUnlessGranted(TreeFolderPanelStrategy::ROLE_ACCESS_DELETE_MEDIA_FOLDER, $folder);
            $folderManager = $this->get('open_orchestra_media_admin.manager.media_folder');

            if (!$folderManager->isDeletable($folder)) {
                throw new FolderNotDeletableException();
            }
            $folderManager->deleteTree($folder);

            $event = $this->get("open_orchestra_media_admin.event.folder_event");
            $event->setFolder($folder);
            $this->dispatchEvent(FolderEvents::FOLDER_DELETE, $event);
            $this->get('object_manager')->flush();
        }

        return array();
    }

    /**
     * @param string $siteId
     *
     * @Config\Route("/list/tree/{siteId}", name="open_orchestra_api_folder_list_tree")
     * @Config\Method({"GET"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_MEDIA_FOLDER')")
     *
     * @return FacadeInterface
     */
    public function listTreeFolderAction($siteId)
    {
        $folders = $this->get('open_orchestra_media.repository.media_folder')->findAllRootFolderBySiteId($siteId);
        $transformer = $this->get('open_orchestra_api.transformer_manager')->get('folder_tree');

        return $transformer->transform($folders);
    }
}
