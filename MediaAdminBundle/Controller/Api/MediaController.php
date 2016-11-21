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
use Symfony\Component\Validator\ConstraintViolationListInterface;
use OpenOrchestra\MediaAdminBundle\Context\MediaAdminGroupContext;
use OpenOrchestra\Media\Model\MediaInterface;

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
     * @return FacadeInterface
     *
     * @Config\Route("/{mediaId}", name="open_orchestra_api_media_show")
     * @Config\Method({"GET"})
     *
     * @Api\Groups({MediaAdminGroupContext::MEDIA_ALTERNATIVES, MediaAdminGroupContext::MEDIA_ADVANCED_LINKS})
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
     * @return Response
     * @throws MediaNotDeletableException
     */
    public function deleteAction($mediaId)
    {
        $media = $this->get('open_orchestra_media.repository.media')->find($mediaId);

        if ($media instanceof MediaInterface) {
            if ($media->isUsed()) {
                throw new MediaNotDeletableException();
            }

            $documentManager = $this->get('object_manager');
            $documentManager->remove($media);
            $documentManager->flush();

            $this->dispatchEvent(MediaEvents::MEDIA_DELETE, new MediaEvent($media));
        }

        return array();
    }

    /**
     * @param Request $request
     * @param string  $folderId
     *
     * @Config\Route("/upload/{folderId}", name="open_orchestra_api_media_upload")
     * Config\Method({"POST"})
     *
     * @return FacadeInterface|Response
     */
    public function uploadAction($folderId, Request $request)
    {
        $uploadedFile = $request->files->get('file');
        $saveMediaManager = $this->get('open_orchestra_media_admin.manager.save_media');

        if ($uploadedFile && $uploadedFile = $saveMediaManager->getFileFromChunks($uploadedFile)) {
            $media = $saveMediaManager->initializeMediaFromUploadedFile($uploadedFile, $folderId);
            $violations = $this->get('validator')->validate($media, null, array('upload'));
            if (count($violations) !== 0) {
                return new Response(
                    implode('.', $this->getViolationsMessage($violations)),
                    403
                );
            }

            $saveMediaManager->saveMedia($media);

            return $this->get('open_orchestra_api.transformer_manager')->get('media')->transform($media);

        }

        return new Response('', 202);
    }

    /**
     * @param string $folderId
     *
     * @Config\Route("/{folderId}/media-types", name="open_orchestra_api_media_type_list")
     * Config\Method({"POST"})
     *
     * @return FacadeInterface|Response
     */
    public function mediaTypeListAction($folderId)
    {
        /** @var FolderInterface $folder */
        $folder = $this->get('open_orchestra_media.repository.media_folder')->find($folderId);
        $this->denyAccessUnlessGranted(TreeFolderPanelStrategy::ROLE_ACCESS_MEDIA_FOLDER, $folder);

        $mediaCollection = $this->get('open_orchestra_media.repository.media')->findByFolderId($folderId);

        return $this->get('open_orchestra_api.transformer_manager')
            ->get('media_type_collection')->transform($mediaCollection, $folderId);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     *
     * @return array
     */
    protected function getViolationsMessage(ConstraintViolationListInterface $violations)
    {
        $messages = array();
        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }

        return $messages;
    }
}
