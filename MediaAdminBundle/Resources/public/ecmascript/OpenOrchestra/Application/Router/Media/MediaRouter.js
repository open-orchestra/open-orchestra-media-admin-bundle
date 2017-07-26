import AbstractMediaRouter   from 'OpenOrchestra/Application/Router/AbstractMediaRouter'
import Medias                from 'OpenOrchestra/Application/Collection/Media/Medias'
import MediasView            from 'OpenOrchestra/Application/View/Media/MediasView'
import MediaFormView         from 'OpenOrchestra/Application/View/Media/MediaFormView'
import MediaImageFormView    from 'OpenOrchestra/Application/View/Media/MediaImageFormView'
import MediaUploadView       from 'OpenOrchestra/Application/View/Media/MediaUploadView'
import Application           from 'OpenOrchestra/Application/Application'
import FormBuilder           from 'OpenOrchestra/Service/Form/Model/FormBuilder'

/**
 * @class MediaRouter
 */
class MediaRouter extends AbstractMediaRouter
{
    /**
     * @inheritdoc
     */
    preinitialize(options) {
        this.routes = {
            'media/list(/:page)'            : 'listMedia',
            'media/new'                     : 'newMedia',
            'media/edit/:mediaType/:mediaId': 'editMedia',
        };

        Application.getConfiguration().addParameter('mediaViews', {'image': MediaImageFormView});
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
            '*' : 'navigation-media'
        };
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
            urlParameter: {
                siteId: Application.getContext().get('siteId')
            },
            data : {
                start: page * pageLength,
                length: pageLength,
                order: {
                    name :'updated_at',
                    dir: 'desc'
                }
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
                    selectionMod: false,
                    siteId: Application.getContext().get('siteId')
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
}

export default MediaRouter;
