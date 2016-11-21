<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Admin;

use OpenOrchestra\BackofficeBundle\Controller\AbstractAdminController;
use OpenOrchestra\MediaAdmin\FolderEvents;
use OpenOrchestra\Media\Model\FolderInterface;
use OpenOrchestra\MediaAdminBundle\NavigationPanel\Strategies\TreeFolderPanelStrategy;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Component\HttpFoundation\Response;

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

        $url = $this->generateUrl('open_orchestra_media_admin_folder_form', array('folderId' => $folderId));
        $message = $this->get('translator')->trans('open_orchestra_media_admin.form.folder.success');

        $form = $this->generateForm($folder, $url, TreeFolderPanelStrategy::ROLE_ACCESS_UPDATE_MEDIA_FOLDER);
        $form->handleRequest($request);
        $event = $this->get("open_orchestra_media_admin.event.folder_event");
        $event->setFolder($folder);
        if ($this->handleForm($form, $message, $folder)) {
            $this->dispatchEvent(FolderEvents::FOLDER_UPDATE, $event);
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @param Request $request
     * @param string  $parentId
     *
     * @Config\Route("/folder/new/{parentId}", name="open_orchestra_media_admin_folder_new")
     * @Config\Method({"GET", "POST"})
     *
     * @return Response
     */
    public function newAction(Request $request, $parentId)
    {
        $parentFolder = $this->container->get('open_orchestra_media.repository.media_folder')->find($parentId);
        $this->denyAccessUnlessGranted(TreeFolderPanelStrategy::ROLE_ACCESS_CREATE_MEDIA_FOLDER, $parentFolder);
        $folderClass = $this->container->getParameter('open_orchestra_media.document.media_folder.class');
        /** @var FolderInterface $folder */
        $folder = new $folderClass();
        if ($parentFolder) {
            $folder->setParent($parentFolder);
        }
        $siteId = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteId();
        $folder->setSiteId($siteId);

        $url = $this->generateUrl('open_orchestra_media_admin_folder_new', array('parentId' => $parentId));
        $message = $this->get('translator')->trans('open_orchestra_media_admin.form.folder.success');

        $form = $this->generateForm($folder, $url);
        $form->handleRequest($request);

        $event = $this->get("open_orchestra_media_admin.event.folder_event");
        $event->setFolder($folder);
        if ($this->handleForm($form, $message, $folder)) {
            $this->dispatchEvent(FolderEvents::FOLDER_UPDATE, $event);

            $response = new Response('', Response::HTTP_CREATED, array('Content-type' => 'text/html; charset=utf-8', 'document-id' => $folder->getId()));

            return $this->render('BraincraftedBootstrapBundle::flash.html.twig', array(), $response);
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @Config\Route("/folder/list/{mediaType}", defaults={"mediaType" = ""}, name="open_orchestra_media_admin_media_list_form")
     * @Config\Method({"GET"})
     *
     * @return Response
     */
    public function showFoldersAction($mediaType)
    {
        $siteId = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteId();
        $rootFolders = $this->get('open_orchestra_media.repository.media_folder')->findAllRootFolderBySiteId($siteId);
        return $this->render( 'OpenOrchestraMediaAdminBundle:Tree:showModalFolderTree.html.twig', array(
            'folders' => $rootFolders,
            'mediaType' => $mediaType
        ));
    }

    /**
     * @param FolderInterface $folder
     * @param string          $url
     * @param string|null     $editionRole
     *
     * @return Form
     */
    protected function generateForm(FolderInterface $folder, $url, $editionRole = null)
    {
        $form = $this->createForm('oo_folder', $folder, array('action' => $url), $editionRole);

        return $form;
    }
}
