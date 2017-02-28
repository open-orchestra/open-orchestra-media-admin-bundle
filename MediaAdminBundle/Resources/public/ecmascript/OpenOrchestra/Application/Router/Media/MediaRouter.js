import OrchestraRouter    from '../OrchestraRouter'
import Medias             from '../../Collection/Media/Medias'
import MediasView         from '../../View/Media/MediasView'
import MediaFormView      from '../../View/Media/MediaFormView'
import MediaImageFormView from '../../View/Media/MediaImageFormView'
import MediaUploadView    from '../../View/Media/MediaUploadView'
import Application        from '../../Application'
import FormBuilder        from '../../../Service/Form/Model/FormBuilder'
import FoldersTree        from '../../Collection/Folder/FoldersTree'
import FoldersTreeView    from '../../View/Media/FoldersTreeView'

/**
 * @class MediaRouter
 */
class MediaRouter extends OrchestraRouter
{
    /**
     * @inheritdoc
     */
    preinitialize(options) {
        this.routes = {
            'media/list(/:page)'            : 'listMedia',
            'media/new'                     : 'newMedia',
            'media/edit/:mediaType/:mediaId': 'editMedia',
            'folder/list'                   : 'listFolders',
            'folder/edit/:folderId'         : 'editFolder',
            'folder/new/:parentId'          : 'newFolder'
        };

        Application.getConfiguration().addParameter('mediaViews', {'image': MediaImageFormView});
    }

    /**
     * @inheritdoc
     */
    getBreadcrumb() {
        return [
            {
                label:Translator.trans('open_orchestra_backoffice.navigation.contribution.title')
            },
            {
                label: Translator.trans('open_orchestra_media_admin.navigation.contribution.media')
            },
            [
                {
                    label: Translator.trans('open_orchestra_media_admin.media.title_list'),
                    link: '#'+Backbone.history.generateUrl('listMedia')
                },
                {
                    label: Translator.trans('open_orchestra_media_admin.folder.title_list'),
                    link: '#'+Backbone.history.generateUrl('listFolders')
                }
            ]
        ]
    }

    /**
     *  List Media
     *
     * @param {String} page
     */
    listMedia(page) {
        if (null === page) {
            page = 1
        }
        this._displayLoader(Application.getRegion('content'));
        let pageLength = 10;
        page = Number(page) - 1;
        new Medias().fetch({
            data : {
                start: page * pageLength,
                length: pageLength
            },
            success: (medias) => {
                let mediasView = new MediasView({
                    collection: medias,
                    settings: {
                        page: page,
                        deferLoading: [medias.recordsTotal, medias.recordsFiltered],
                        data: medias.models,
                        pageLength: pageLength
                    },
                    selectionMod: false
                });
                let el = mediasView.render().$el;
                Application.getRegion('content').html(el);
            }
        });
    }

    /**
     * New media
     */
    newMedia() {
        let mediaUploadView = new MediaUploadView();
        Application.getRegion('content').html(mediaUploadView.render().$el);
    }

    /**
     * Edit media
     */
    editMedia(mediaType, mediaId) {
        let url = Routing.generate('open_orchestra_media_admin_media_form', {mediaId: mediaId});
        this._displayLoader(Application.getRegion('content'));
        FormBuilder.createFormFromUrl(url, (form) => {
            let formViewClass = MediaFormView;
            if (typeof Application.getConfiguration().getParameter('mediaViews')[mediaType] !== 'undefined') {
                formViewClass = Application.getConfiguration().getParameter('mediaViews')[mediaType];
            }
            let mediaFormView = new formViewClass({
                form: form,
                mediaId: mediaId
            });
            Application.getRegion('content').html(mediaFormView.render().$el);
        });
    }

    /**
     *  List Folders
     */
    listFolders() {
        this._displayLoader(Application.getRegion('content'));
        new FoldersTree().fetch({
            siteId: Application.getContext().siteId,
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
        alert('Edit folder ' + folderId);
    }

    /**
     *  New Folder
     *
     * @param {String} parentId
     */
    newFolder(parentId) {
        alert('New folder under ' + parentId);
    }
}

export default MediaRouter;
