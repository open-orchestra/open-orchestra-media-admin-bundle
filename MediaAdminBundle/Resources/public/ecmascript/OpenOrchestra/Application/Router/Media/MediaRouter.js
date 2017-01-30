import OrchestraRouter  from '../OrchestraRouter'
import Medias               from '../../Collection/Media/Medias'
import MediasView           from '../../View/Media/MediasView'
import Application            from '../../Application'
//import StatusFormView         from '../../View/Status/StatusFormView'
//import FormBuilder            from '../../../Service/Form/Model/FormBuilder'

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
            'media/list(/:page)' : 'listMedia',
            'media/new'          : 'newMedia',
            'media/edit/:mediaId': 'editMedia',
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
//        let url = Routing.generate('open_orchestra_workflow_admin_status_new');
//
//        this._displayLoader(Application.getRegion('content'));
//        FormBuilder.createFormFromUrl(url, (form) => {
//            let statusFormView = new StatusFormView({
//                form: form,
//                name: Translator.trans('open_orchestra_workflow_admin.status.title_new')
//             });
//            Application.getRegion('content').html(statusFormView.render().$el);
//        });
    }

    /**
     * Edit Media
     *
     * @param  {String} mediaId
     */
    editMedia(mediaId) {
//        let url = Routing.generate('open_orchestra_workflow_admin_status_form', {statusId: statusId});
//        this._displayLoader(Application.getRegion('content'));
//        FormBuilder.createFormFromUrl(url, (form) => {
//            let statusFormView = new StatusFormView({
//                form: form,
//                name: name,
//                statusId: statusId
//            });
//            Application.getRegion('content').html(statusFormView.render().$el);
//        });
    }
}

export default MediaRouter;
