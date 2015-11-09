<?php

namespace OpenOrchestra\MediaAdminBundle\Controller\Admin;

use OpenOrchestra\BackofficeBundle\Controller\AbstractAdminController;
use OpenOrchestra\Media\Event\FolderEvent;
use OpenOrchestra\Media\FolderEvents;
use OpenOrchestra\Media\Model\FolderInterface;
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
     * @Config\Security("is_granted('ROLE_ACCESS_UPDATE_MEDIA_FOLDER')")
     *
     * @return Response
     */
    public function formAction(Request $request, $folderId)
    {
        $folderRepository = $this->get('open_orchestra_media.repository.media_folder');
        $folder = $folderRepository->find($folderId);

        $url = $this->generateUrl('open_orchestra_media_admin_folder_form', array('folderId' => $folderId));
        $message = $this->get('translator')->trans('open_orchestra_media_admin.form.folder.success');

        $form = $this->generateForm($folder, $url);
        $form->handleRequest($request);

        if ($this->handleForm($form, $message, $folder)) {
            $this->dispatchEvent(FolderEvents::FOLDER_UPDATE, new FolderEvent($folder));
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
     * @Config\Security("is_granted('ROLE_ACCESS_CREATE_MEDIA_FOLDER')")
     *
     * @return Response
     */
    public function newAction(Request $request, $parentId)
    {
        $parentFolder = $this->container->get('open_orchestra_media.repository.media_folder')->find($parentId);
        $folderClass = $this->container->getParameter('open_orchestra_media.document.media_folder.class');
        /** @var FolderInterface $folder */
        $folder = new $folderClass();
        if ($parentFolder) {
            $folder->setParent($parentFolder);
        }

        $url = $this->generateUrl('open_orchestra_media_admin_folder_new', array('parentId' => $parentId));
        $message = $this->get('translator')->trans('open_orchestra_media_admin.form.folder.success');

        $form = $this->generateForm($folder, $url);
        $form->handleRequest($request);

        if ($this->handleForm($form, $message, $folder)) {
            $url = $this->generateUrl('open_orchestra_media_admin_folder_form', array('folderId' => $folder->getId()));
            $this->dispatchEvent(FolderEvents::FOLDER_UPDATE, new FolderEvent($folder));

            return $this->redirect($url);
        }

        return $this->renderAdminForm($form);
    }

    /**
     * @Config\Route("/folder/list", name="open_orchestra_media_admin_media_list_form")
     * @Config\Method({"GET"})
     *
     * @Config\Security("is_granted('ROLE_ACCESS_MEDIA_FOLDER')")
     *
     * @return Response
     */
    public function showFoldersAction()
    {
        $siteId = $this->get('open_orchestra_backoffice.context_manager')->getCurrentSiteId();
        $rootFolders = $this->get('open_orchestra_media.repository.media_folder')->findAllRootFolderBySiteId($siteId);
        return $this->render( 'OpenOrchestraMediaAdminBundle:Tree:showModalFolderTree.html.twig', array(
            'folders' => $rootFolders,
        ));
    }

    /**
     * @param FolderInterface $folder
     * @param string          $url
     *
     * @return Form
     */
    protected function generateForm(FolderInterface $folder, $url)
    {
        $form = $this->createForm('oo_folder', $folder, array('action' => $url));

        return $form;
    }
}
