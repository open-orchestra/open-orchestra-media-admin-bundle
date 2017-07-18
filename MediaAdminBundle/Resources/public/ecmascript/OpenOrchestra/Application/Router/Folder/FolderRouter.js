import AbstractMediaRouter from 'OpenOrchestra/Application/Router/AbstractMediaRouter'
import Application         from 'OpenOrchestra/Application/Application'
import FoldersTree         from 'OpenOrchestra/Application/Collection//Folder/FoldersTree'
import FoldersTreeView     from 'OpenOrchestra/Application/View/Folder/FoldersTreeView'
import FormBuilder         from 'OpenOrchestra/Service/Form/Model/FormBuilder'
import FolderFormView      from 'OpenOrchestra/Application/View/Folder/FolderFormView'

/**
 * @class MediaRouter
 */
class FolderRouer extends AbstractMediaRouter
{
    /**
     * @inheritdoc
     */
    preinitialize(options) {
        this.routes = {
            'folder/list'           : 'listFolders',
            'folder/edit/:folderId' : 'editFolder',
            'folder/new(/:parentId)': 'newFolder'
        };
    }

    /**
     * @inheritdoc
     */
    getMenuHighlight() {
        return {
            '*' : 'navigation-media-library'
        };
    }

    /**
     * @inheritdoc
     */
    getBreadcrumbHighlight() {
        return {
            '*' : 'navigation-folder'
        };
    }

    /**
     *  List Folders
     */
    listFolders() {
        this._displayLoader(Application.getRegion('content'));
        new FoldersTree().fetch({
            siteId: Application.getContext().get('siteId'),
            success: (foldersTree) => {
                let foldersTreeView = new FoldersTreeView({
                    foldersTree: foldersTree,
                    settings: {data: foldersTree.models}
                });
                let el = foldersTreeView.render().$el;
                Application.getRegion('content').html(el);
            }
        });
    }

    /**
     *  Edit Folder
     *
     * @param {String} folderId
     */
    editFolder(folderId) {
        this._displayLoader(Application.getRegion('content'));
        let url = Routing.generate('open_orchestra_media_admin_folder_form', {folderId: folderId});
        FormBuilder.createFormFromUrl(url, (form) => {
            let folderFormView = new FolderFormView({
                form: form,
                folderId: folderId
            });
            Application.getRegion('content').html(folderFormView.render().$el);
        });
    }

    /**
     *  New Folder
     *
     * @param {String} parentId
     */
    newFolder(parentId) {
        this._displayLoader(Application.getRegion('content'));
        let url = Routing.generate('open_orchestra_media_admin_folder_new');
        if (parentId) {
            url = Routing.generate('open_orchestra_media_admin_folder_add', {parentId: parentId});
        }
        FormBuilder.createFormFromUrl(url, (form) => {
            let folderFormView = new FolderFormView({
                form : form
            });
            Application.getRegion('content').html(folderFormView.render().$el);
        });
    }
}

export default FolderRouer;
