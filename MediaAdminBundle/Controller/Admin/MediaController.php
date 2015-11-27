<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Admin;

use OpenOrchestra\BackofficeBundle\Controller\AbstractAdminController;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use OpenOrchestra\Media\Model\MediaInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MediaController
 */
class MediaController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param string  $mediaId
     *
     * @Config\Route("/media/{mediaId}/crop", name="open_orchestra_media_admin_media_crop")
     * @Config\Method({"GET", "POST"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_UPDATE_MEDIA')")
     *
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function cropAction(Request $request, $mediaId)
    {
        $form = $this->createForm('oo_media_crop', array('id' => $mediaId), array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_crop', array(
                'mediaId' => $mediaId,
            ))
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $mediaRepository = $this->get('open_orchestra_media.repository.media');
            /** @var MediaInterface $media */
            $media = $mediaRepository->find($mediaId);

            $uploadedMediaManager = $this->get('open_orchestra_media_file.manager.uploaded_media');
            $filename = $media->getFilesystemName();
            $this->get('filesystem')->dumpFile(
                $this->container->getParameter('open_orchestra_media.tmp_dir') . '/' . $filename,
                $uploadedMediaManager->getFileContent($filename)
            );

            $this->get('open_orchestra_media_admin.manager.image_resizer')->crop(
                $media,
                $data['x'],
                $data['y'],
                $data['h'],
                $data['w'],
                $data['format']
            );

            $this->dispatchEvent(MediaEvents::MEDIA_CROP, new MediaEvent($media));
        }


        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $format
     * @param string  $mediaId
     *
     * @Config\Route("/media/override/{mediaId}/{format}", name="open_orchestra_media_admin_media_override")
     * @Config\Method({"GET", "POST"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_UPDATE_MEDIA')")
     *
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function overrideAction(Request $request, $format, $mediaId)
    {
        $mediaRepository = $this->get('open_orchestra_media.repository.media');
        $media = $mediaRepository->find($mediaId);

        $form = $this->createForm('oo_media', null, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_override', array(
                'mediaId' => $mediaId,
                'format' => $format
            ))
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form->getData()->getFile();
            $tmpDir = $this->container->getParameter('open_orchestra_media.tmp_dir');
            $file->move($tmpDir, $format . '-' . $media->getFilesystemName());
            $this->get('open_orchestra_media_admin.manager.image_resizer')->override($media, $format);

            $this->dispatchEvent(MediaEvents::MEDIA_CROP, new MediaEvent($media));
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $mediaId
     *
     * @Config\Route("/media/{mediaId}/meta", name="open_orchestra_media_admin_media_meta")
     * @Config\Method({"GET", "POST"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_UPDATE_MEDIA')")
     *
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function metaAction(Request $request, $mediaId)
    {
        $mediaRepository = $this->get('open_orchestra_media.repository.media');
        $media = $mediaRepository->find($mediaId);

        $form = $this->createForm('oo_media_meta', $media, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_meta', array(
                'mediaId' => $mediaId,
            ))
        ));

        $form->handleRequest($request);

        $this->handleForm($form, $this->get('translator')->trans('open_orchestra_media_admin.form.media.success'));

        return $this->renderAdminForm($form);
    }
}
