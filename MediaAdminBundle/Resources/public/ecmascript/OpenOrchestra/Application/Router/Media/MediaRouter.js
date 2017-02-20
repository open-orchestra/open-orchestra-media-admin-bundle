import OrchestraRouter    from '../OrchestraRouter'
import Medias             from '../../Collection/Media/Medias'
import MediasView         from '../../View/Media/MediasView'
import MediaFormView      from '../../View/Media/MediaFormView'
import MediaImageFormView from '../../View/Media/MediaImageFormView'
import MediaUploadView    from '../../View/Media/MediaUploadView'
import Application        from '../../Application'
import Configuration      from '../../../Service/Configuration'
import FormBuilder        from '../../../Service/Form/Model/FormBuilder'

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
            'media/edit/:mediaType/:mediaId': 'editMedia'
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
                    }
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
    	console.log(Configuration);
        Configuration.addParameter('mediaViews', {'image': MediaImageFormView});

        let url = Routing.generate('open_orchestra_media_admin_media_form', {mediaId: mediaId});
        this._displayLoader(Application.getRegion('content'));
        FormBuilder.createFormFromUrl(url, (form) => {
            let formViewClass = MediaFormView;
            if (typeof Configuration.getParameter('mediaViews')[mediaType] !== 'undefined') {
                formViewClass = Configuration.getParameter('mediaViews')[mediaType][mediaType]
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
