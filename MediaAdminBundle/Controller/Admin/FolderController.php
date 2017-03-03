<?php
namespace OpenOrchestra\MediaAdminBundle\Controller\Admin;
use OpenOrchestra\BackofficeBundle\Controller\AbstractAdminController;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\Media\Model\FolderInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Response;
use OpenOrchestra\Backoffice\Security\ContributionActionInterface;
/**
 * Class FolderController
 */
class FolderController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @param string  $folderId
     *
     * @Config\Route("/folder/form/{folderId}", name="open_orchestra_media_admin_folder_form")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function formAction(Request $request, $folderId)
    {
        $folderRepository = $this->get('open_orchestra_media.repository.media_folder');
        $folder = $folderRepository->find($folderId);
        $this->denyAccessUnlessGranted(ContributionActionInterface::EDIT, $folder);
        $form = $this->createForm('oo_folder', $folder, array(
            'action' => $this->generateUrl('open_orchestra_media_admin_folder_form', array('folderId' => $folderId)),
            'delete_button' => ($this->isGranted(ContributionActionInterface::DELETE, $folder) && $this->get('open_orchestra_media_admin.manager.media_folder')->isDeletable($folder)),
        ));
        $form->handleRequest($request);
        if ($this->handleForm($form, $this->get('translator')->trans('open_orchestra_media_admin.form.folder.success'), $folder)) {
            $event = $this->get("open_orchestra_media_admin.event.folder_event");
            $event->setFolder($folder);
            $this->dispatchEvent(FolderEvents::FOLDER_UPDATE, $event);
        }
        return $this->renderAdminForm($form);
    }
    /**
     * @param Request $request
     * @param string  $parentId
     *
     * @Config\Route("/folder/new", name="open_orchestra_media_admin_folder_new")
     * @Config\Route("/folder/new/{parentId}", name="open_orchestra_media_admin_folder_add")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request, $parentId=null)
    {
        $folderClass = $this->container->getParameter('open_orchestra_media.document.media_folder.class');
        $folder = new $folderClass();
        if (!is_null($parentId)) {
            $parentFolder = $this->container->get('open_orchestra_media.repository.media_folder')->find($parentId);
            $this->denyAccessUnlessGranted(ContributionActionInterface::CREATE, $parentFolder);
            if ($parentFolder) {
                $folder->setParent($parentFolder);
            }
        }
        $this->denyAccessUnlessGranted(ContributionActionInterface::CREATE, $folder);
        $siteId = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteId();
        $folder->setSiteId($siteId);
        $url = $this->generateUrl('open_orchestra_media_admin_folder_new');
        if (!is_null($parentId)) {
            $url = $this->generateUrl('open_orchestra_media_admin_folder_add', array('parentId' => $parentId));
        }
        $form = $this->createForm('oo_folder', $folder, array(
            'action' => $url,
            'new_button' => true,
        ));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $documentManager = $this->get('object_manager');
            $documentManager->persist($folder);
            $documentManager->flush();
            $event = $this->get("open_orchestra_media_admin.event.folder_event");
            $event->setFolder($folder);
            $this->dispatchEvent(FolderEvents::FOLDER_CREATE, $event);
            $message = $this->get('translator')->trans('open_orchestra_media_admin.form.folder.success');
            $response = new Response(
                $message,
                Response::HTTP_CREATED,
                array('Content-type' => 'text/plain; charset=utf-8', 'folderId' => $folder->getId(), 'name' => $folder->getName())
            );
            return $response;
        }
        return $this->renderAdminForm($form);
    }
}