<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Api;

use OpenOrchestra\BaseApiBundle\Controller\BaseController;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use OpenOrchestra\MediaAdminBundle\Exceptions\HttpException\MediaNotDeletableException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use OpenOrchestra\MediaAdminBundle\Context\MediaAdminGroupContext;
use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
use OpenOrchestra\Pagination\Configuration\PaginateFinderConfiguration;
use OpenOrchestra\MediaAdminBundle\Exceptions\MediaNotFoundException;

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
     * @param Request $request
     *
     * @Config\Route("", name="open_orchestra_api_media_list")
     * @Config\Method({"GET"})
     * @Config\Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Api\Groups({MediaAdminGroupContext::MEDIA_ALTERNATIVES})
     *
     * @return FacadeInterface
     */
    public function listAction(Request $request)
    {
        $configuration = PaginateFinderConfiguration::generateFromRequest($request);
        if ($request->get('filter') && isset($request->get('filter')['type'])) {
            $configuration->addSearch('type', $request->get('filter')['type']);
        }
        $repository = $this->get('open_orchestra_media.repository.media');
        $collection = $repository->findForPaginate($configuration);
        if ($request->get('filter') && isset($request->get('filter')['type'])) {
            $recordsTotal = $repository->count($request->get('filter')['type']);
        } else {
            $recordsTotal = $repository->count();
        }
        $recordsFiltered = $repository->countWithFilter($configuration);
        $collectionTransformer = $this->get('open_orchestra_api.transformer_manager')->get('media_collection');
        $facade = $collectionTransformer->transform($collection);
        $facade->recordsTotal = $recordsTotal;
        $facade->recordsFiltered = $recordsFiltered;

        return $facade;
    }

    /**
     * @param Request $request
     *
     * @Config\Route("/delete-multiple", name="open_orchestra_api_media_delete_multiple")
     * @Config\Method({"DELETE"})
     *
     * @return Response
     */
    public function deleteMediasAction(Request $request)
    {
        $format = $request->get('_format', 'json');

        $facade = $this->get('jms_serializer')->deserialize(
            $request->getContent(),
            $this->getParameter('open_orchestra_media_admin.facade.media_collection.class'),
            $format
            );

        $mediaRepository = $this->get('open_orchestra_media.repository.media');
        $medias = $this->get('open_orchestra_api.transformer_manager')->get('media_collection')->reverseTransform($facade);

        $mediasIds = array();
        foreach ($medias as $media) {
            if ($this->isDeleteGranted($media)) {
                $mediasIds[] = $media->getId();
                $this->dispatchEvent(MediaEvents::MEDIA_DELETE, new MediaEvent($media));
            }
        }
        $mediaRepository->removeMedias($mediasIds);

        return array();
    }

    /**
     * Check if current user can delete $media
     *
     * @param MediaInterface $media
     *
     * @return boolean
     */
    protected function isDeleteGranted(MediaInterface $media)
    {
        return ($this->isGranted(ContributionActionInterface::DELETE, $media)
            && !$media->isUsed()
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
        $this->denyAccessUnlessGranted(ContributionActionInterface::READ, $folder);

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
