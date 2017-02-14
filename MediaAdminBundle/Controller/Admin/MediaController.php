<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Admin;

use OpenOrchestra\BackofficeBundle\Controller\AbstractAdminController;
use OpenOrchestra\MediaAdmin\Event\MediaEvent;
use OpenOrchestra\MediaAdmin\MediaEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;

/**
 * Class MediaController
 */
class MediaController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param string  $mediaId
     *
     * @Config\Route("/media/{mediaId}", name="open_orchestra_media_admin_media_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $mediaId)
    {
        $media = $this->get('open_orchestra_media.repository.media')->find($mediaId);
        $this->denyAccessUnlessGranted(ContributionActionInterface::EDIT, $media);

        $formTypeAlias = $this->get('open_orchestra_media_admin.media_form.manager')->getFormType($media);

        $form = $this->createForm($formTypeAlias, $media, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_media_form', array(
                'mediaId' => $mediaId,
            ))
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->dispatchEvent(MediaEvents::MEDIA_UPDATE, new MediaEvent($media));
            $this->get('open_orchestra_media_admin.media_form.manager')->runAdditionalProcess($media, $form);
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('open_orchestra_media_admin.form.media.success')
            );
        }

        return $this->renderAdminForm($form);
    }
}
