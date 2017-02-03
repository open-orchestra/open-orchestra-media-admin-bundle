import OrchestraRouter from '../OrchestraRouter'
import Medias          from '../../Collection/Media/Medias'
import MediasView      from '../../View/Media/MediasView'
import Application     from '../../Application'

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
            'media/list(/:page)' : 'listMedia'
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
}

export default MediaRouter;
