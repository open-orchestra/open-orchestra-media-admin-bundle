<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Api;

use OpenOrchestra\BaseApiBundle\Controller\BaseController;
use OpenOrchestra\BaseApi\Facade\FacadeInterface;
use OpenOrchestra\Media\Event\MediaEvent;
use OpenOrchestra\Media\MediaEvents;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\BaseApiBundle\Controller\Annotation as Api;
use OpenOrchestra\MediaAdminBundle\Exceptions\HttpException\MediaNotDeletableException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Flow\Basic as FlowBasic;

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
     * @param Request $request
     *
     * @Config\Route("", name="open_orchestra_api_media_list")
     * @Config\Method({"GET"})
     *
     * @return FacadeInterface
     */
    public function listAction(Request $request)
    {
        $folderId = $request->get('folderId');
        /** @var FolderInterface $folder */
        $folder = $this->get('open_orchestra_media.repository.media_folder')->find($folderId);
        $folderDeletable = $this->get('open_orchestra_media_admin.manager.media_folder')->isDeletable($folder);
        $parentId = null;
        if ($folder->getParent() instanceof FolderInterface) {
            $parentId = $folder->getParent()->getId();
        }
        $mediaCollection = $this->get('open_orchestra_media.repository.media')->findByFolderId($folderId);

        return $this->get('open_orchestra_api.transformer_manager')->get('media_collection')->transform(
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

        $this->dispatchEvent(MediaEvents::MEDIA_DELETE, new MediaEvent($media));
        $documentManager = $this->get('object_manager');
        $documentManager->remove($media);
        $documentManager->flush();

        return array();
    }

    /**
     * @param Request $request
     * @param string  $folderId
     * 
     * @Config\Route("/upload/{folderId}", name="open_orchestra_api_media_upload")
     * Config\Method({"POST"})
     * 
     * @return FacadeInterface
     */
    public function uploadAction($folderId, Request $request)
    {
        $uploadedFile = $request->files->get('file');

        if ($uploadedFile) {
            $tmpDir = $this->getParameter('open_orchestra_media.tmp_dir');
            $fileName = $this->generateTmpName($uploadedFile);

            if (FlowBasic::save($tmpDir . '/' . $fileName, $tmpDir)) {
                $media = $this->createMedia($uploadedFile, $fileName);

                return $this->get('open_orchestra_api.transformer_manager')->get('media')->transform($media);
            }
        }

        return array();
    }

    /**
     * Generate the tmp file name used to glue chunk files
     * 
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * 
     * @return string
     */
    protected function generateTmpName($uploadedFile)
    {
        $tmpDir = $this->getParameter('open_orchestra_media.tmp_dir');

        return sha1(uniqid(mt_rand(), true))
            . pathinfo($tmpDir . '/' . $uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)
            . '.' . $uploadedFile->guessClientExtension();
    }

    /**
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     * @param string                                             $fileName
     * 
     * @return OpenOrchestra\Media\Model\MediaInterface
     */
    protected function createMedia($uploadedFile, $fileName)
    {
        $folderRepository = $this->get('open_orchestra_media.repository.media_folder');
        $folder = $folderRepository->find($folderId);

        $mediaClass = $this->container->getParameter('open_orchestra_media.document.media.class');
        $media = new $mediaClass();
        $media->setMediaFolder($folder);
        $media->setFile($uploadedFile);
        $media->setFilesystemName($fileName);

        $documentManager = $this->get('object_manager');
        $documentManager->persist($media);
        $documentManager->flush();

        return $media;
    }
}
