<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Api;

use OpenOrchestra\BaseApiBundle\Controller\BaseController;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use OpenOrchestra\MediaAdminBundle\Exceptions\HttpException\MediaNotDeletableException;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MediaController
 *
 * @Config\Route("media")
 *
 * @Api\Serialize()
 */
class MediaController extends BaseController
{
    /**
     * @param int $mediaId
     *
     * @Config\Route("/{mediaId}", name="open_orchestra_api_media_show")
     * @Config\Method({"GET"})
     *
     * @return FacadeInterface
     */
    public function showAction($mediaId)
    {
        $media = $this->get('open_orchestra_media.repository.media')->find($mediaId);

        return $this->get('open_orchestra_api.transformer_manager')->get('media')->transform($media);
    }

    /**
     * @param string $folderId
     * @param string $mediaType
     *
     * @Config\Route("/folder/{folderId}/{mediaType}", defaults={"mediaType" = ""}, name="open_orchestra_api_media_list")
     * @Config\Method({"GET"})
     *
     * @return FacadeInterface
     */
    public function listAction($folderId, $mediaType)
    {
        /** @var FolderInterface $folder */
        $folder = $this->get('open_orchestra_media.repository.media_folder')->find($folderId);
        $this->denyAccessUnlessGranted(TreeFolderPanelStrategy::ROLE_ACCESS_MEDIA_FOLDER, $folder);

        $folderDeletable = $this->get('open_orchestra_media_admin.manager.media_folder')->isDeletable($folder);

        $parentId = null;
        if ($folder->getParent() instanceof FolderInterface) {
            $parentId = $folder->getParent()->getId();
        }

        if ($mediaType != "") {
            $mediaCollection = $this->get('open_orchestra_media.repository.media')
                ->findByFolderIdAndMediaType($folderId, $mediaType);
        } else {
            $mediaCollection = $this->get('open_orchestra_media.repository.media')
                ->findByFolderId($folderId);
        }

        return $this->get('open_orchestra_api.transformer_manager')
            ->get('media_collection')->transform(
                $mediaCollection,
                $folderId,
                $folderDeletable,
                $parentId
        );
    }

    /**
     * @param $mediaId
     *
     * @Config\Route("/{mediaId}/delete", name="open_orchestra_api_media_delete")
     * @Config\Method({"DELETE"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_DELETE_MEDIA')")
     *
     * @return Response
     * @throws MediaNotDeletableException
     */
    public function deleteAction($mediaId)
    {
        $media = $this->get('open_orchestra_media.repository.media')->find($mediaId);
        if (!$media->isDeletable()) {
            throw new MediaNotDeletableException();
        }

        $documentManager = $this->get('object_manager');
        $documentManager->remove($media);
        $documentManager->flush();

        $this->dispatchEvent(MediaEvents::MEDIA_DELETE, new MediaEvent($media));

        return array();
    }

    /**
     * @param Request $request
     * @param string  $folderId
     *
     * @Config\Route("/upload/{folderId}", name="open_orchestra_api_media_upload")
     * Config\Method({"POST"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_CREATE_MEDIA')")
     *
     * @return FacadeInterface|Response
     */
    public function uploadAction($folderId, Request $request)
    {
        $uploadedFile = $request->files->get('file');
        $saveMediaManager = $this->get('open_orchestra_media_admin.manager.save_media');

        if ($uploadedFile && $filename = $saveMediaManager->getFilenameFromChunks($uploadedFile)) {

            if ($saveMediaManager->isFileAllowed($filename)) {
                $media = $saveMediaManager->createMediaFromUploadedFile($uploadedFile, $filename, $folderId);

                return $this->get('open_orchestra_api.transformer_manager')->get('media')->transform($media);
            }

            $translator = $this->container->get('translator');

            return new Response(
                $translator->trans('open_orchestra_media_admin.form.upload.not_allowed'),
                403
            );
        }

        return new Response('', 202);
    }

    /**
     * @param string folderId
     *
     * @Config\Route("/{folderId}/media-types", name="open_orchestra_api_media_type_list")
     * Config\Method({"POST"})
     *
     * @return FacadeInterface|Response
     */
    public function mediaTypeListAction($folderId)
    {
        $mediaCollection = $this->get('open_orchestra_media.repository.media')->findByFolderId($folderId);

        return $this->get('open_orchestra_api.transformer_manager')
            ->get('media_type_collection')->transform($mediaCollection, $folderId);
    }
}
