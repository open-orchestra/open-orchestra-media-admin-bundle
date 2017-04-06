<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Api;

use OpenOrchestra\Backoffice\BusinessRules\Strategies\BusinessActionInterface;
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
use OpenOrchestra\Media\Model\MediaFolderInterface;

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
     * @param string  $siteId
     *
     * @Config\Route("/list/with-perimeter/{siteId}", defaults={"withPerimeter": true}, name="open_orchestra_api_media_list_with_perimeter")
     * @Config\Route("/list/without-perimeter/{siteId}", defaults={"withPerimeter": false}, name="open_orchestra_api_media_list_without_perimeter")
     * @Config\Method({"GET"})
     * @Config\Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Api\Groups({MediaAdminGroupContext::MEDIA_ALTERNATIVES})
     *
     * @return FacadeInterface
     */
    public function listAction(Request $request, $siteId, $withPerimeter)
    {
        $configuration = PaginateFinderConfiguration::generateFromRequest($request);

        if ($withPerimeter && null === $request->get('filter')['folderId']) {
            $configuration->addSearch('perimeterFolderIds', $this->getConnectedUserMediaPerimeter());
        }

        if ($request->get('filter') && isset($request->get('filter')['type'])) {
            $configuration->addSearch('type', $request->get('filter')['type']);
        }
        $repository = $this->get('open_orchestra_media.repository.media');
        $collection = $repository->findForPaginate($configuration, $siteId);
        if ($request->get('filter') && isset($request->get('filter')['type']) && '' !== $request->get('filter')['type']) {
            $recordsTotal = $repository->count($siteId, $request->get('filter')['type']);
        } else {
            $recordsTotal = $repository->count($siteId);
        }
        $recordsFiltered = $repository->countWithFilter($configuration, $siteId);
        $collectionTransformer = $this->get('open_orchestra_api.transformer_manager')->get('media_collection');
        $facade = $collectionTransformer->transform($collection);
        $facade->recordsTotal = $recordsTotal;
        $facade->recordsFiltered = $recordsFiltered;

        return $facade;
    }

    /**
     * Return folder ids included in the allowed perimeter to the user for media contribution
     *
     * @return null|array
     */
    protected function getConnectedUserMediaPerimeter()
    {
        $userGroups = $this->get('security.token_storage')->getToken()->getUser()->getGroups();
        $folderIds = array();

        foreach ($userGroups as $group) {
            if ($group->hasRole('EDITORIAL_MEDIA_CONTRIBUTOR')) {
                foreach ($group->getPerimeter(MediaFolderInterface::ENTITY_TYPE)->getItems() as $path) {
                    foreach ($this->get('open_orchestra_media.repository.media_folder')->findSubTreeByPath($path) as $folder) {
                        $folderIds[$folder->getId()] = $folder->getId();
                    }
                }
            }
        }

        return $folderIds;
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
            if ($this->isGranted(ContributionActionInterface::DELETE, $media) &&
                $this->get('open_orchestra_backoffice.business_rules_manager')->isGranted(BusinessActionInterface::DELETE, $media)) {
                $mediasIds[] = $media->getId();
                $this->dispatchEvent(MediaEvents::MEDIA_DELETE, new MediaEvent($media));
            }
        }
        $mediaRepository->removeMedias($mediasIds);

        return array();
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
            if (!$this->get('open_orchestra_backoffice.business_rules_manager')->isGranted(BusinessActionInterface::DELETE, $media)) {
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
            $siteId = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteId();
            $media = $saveMediaManager->initializeMediaFromUploadedFile($uploadedFile, $folderId, $siteId);
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
