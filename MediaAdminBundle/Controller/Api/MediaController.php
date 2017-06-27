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
use OpenOrchestra\Backoffice\Security\ContributionRoleInterface;
use OpenOrchestra\MediaAdmin\Security\ContributionRoleInterface as MediaContributionRoleInterface;

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
     * @param bool    $withPerimeter
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
        $mapping = array(
            'updated_at' => 'updatedAt',
            'name' => 'name',
            'size' => 'mediaInformations.size',
        );
        $configuration = PaginateFinderConfiguration::generateFromRequest($request, $mapping);

        $foldersId = null;
        if ($withPerimeter && null === $request->get('filter')['folderId']) {
            $foldersId = $this->getConnectedUserMediaPerimeter($siteId);
            $configuration->addSearch('perimeterFolderIds', $foldersId);
        }

        $type = null;
        if ($request->get('filter') && isset($request->get('filter')['type']) && '' !== $request->get('filter')['type']) {
            $type = $request->get('filter')['type'];
            $configuration->addSearch('type', $type);
        }

        $repository = $this->get('open_orchestra_media.repository.media');

        $facade = $this->get('open_orchestra_api.transformer_manager')
            ->transform('media_collection', $repository->findForPaginate($configuration, $siteId));
        $facade->recordsTotal = $repository->count($siteId, $type, $foldersId);
        $facade->recordsFiltered = $repository->countWithFilter($configuration, $siteId);

        return $facade;
    }

    /**
     * Return folder ids included in the allowed perimeter to the user for media contribution
     *
     * @param string $siteId
     * @return null|array
     */
    protected function getConnectedUserMediaPerimeter($siteId)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user->hasRole(ContributionRoleInterface::PLATFORM_ADMIN)
            || $user->hasRole(ContributionRoleInterface::DEVELOPER)
        ) {
            return null;
        }

        $userGroups = $user->getGroups();
        $folderIds = array();

        foreach ($userGroups as $group) {
            if ($group->hasRole(MediaContributionRoleInterface::MEDIA_CONTRIBUTOR) && $group->getSite()->getSiteId() == $siteId) {
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
        $medias = $this->get('open_orchestra_api.transformer_manager')->reverseTransform('media_collection', $facade);

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
        $title = $request->get('title');

        $saveMediaManager = $this->get('open_orchestra_media_admin.manager.save_media');

        if ($uploadedFile && $uploadedFile = $saveMediaManager->getFileFromChunks($uploadedFile)) {
            $siteId = $this->get('open_orchestra_backoffice.context_backoffice_manager')->getSiteId();
            $media = $saveMediaManager->initializeMediaFromUploadedFile($uploadedFile, $folderId, $siteId, $title);
            $violations = $this->get('validator')->validate($media, null, array('upload'));
            if (count($violations) !== 0) {
                return new Response(
                    implode('.', $this->getViolationsMessage($violations)),
                    403
                );
            }

            $saveMediaManager->saveMedia($media);

            return $this->get('open_orchestra_api.transformer_manager')->transform('media', $media);

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
            ->transform('media_type_collection', $mediaCollection, $folderId);
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
