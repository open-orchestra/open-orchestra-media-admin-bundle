import ModalView   from '../../../Service/Modal/View/ModalView'
import Medias      from '../../../Application/Collection/Media/Medias'
import Application from '../../../Application/Application'
import MediasView  from '../../../Application/View/Media/MediasView'

/**
 * @class MediaModalView
 */
class MediaModalView extends ModalView
{
    /**
     * Pre initialize
     */
    preinitialize(options) {
        super.preinitialize(options);
        this._selectCallback = options.selectCallback;
        this.events['click .media-select'] = '_previewMedia';
    }

    render() {
        let page = 0;
        let pageLength = 10;
        let container = this._createModalContainer(Translator.trans('open_orchestra_media_admin.media.title_list'));
        new Medias().fetch({
            data : {
                start: page * pageLength,
                length: pageLength
            },
            success: (medias) => {
            	console.log(medias.models);
                this._medias = {};
                for (i = 0; i < medias.models.length; i++) {
                	console.log(media)
                    this._medias[media.models[i][id]] = media.models[i];
                }
                let mediasView = new MediasView({
                    collection: medias,
                    settings: {
                        page: page,
                        deferLoading: [medias.recordsTotal, medias.recordsFiltered],
                        data: medias.models,
                        pageLength: pageLength,
                        selectionMod: true
                    }
                });
                let el = mediasView.render().$el;
                $('.modal-body', container).append(el);
                this.$el.html(container);
            }
        });

        return this;
    }

    _createModalContainer(title, withFooter = false) {
        let template = this._renderTemplate('Media/Modal/mediaModalContainer', {
            'title': title,
            'withFooter': withFooter
        });

        return $(template);
    }

    _previewMedia(event)
    {
        console.log(this)
        let img = $(event.target);
        let template = this._renderTemplate('Media/Modal/mediaPreviewView', {
            media: null
        });
        this.$el.html(template);
    }

    _selectMedia(event)
    {
        let img = $(event.target);
        this._selectCallback({
            'id': img.data('id'),
            'format': img.data('format'),
            'alt': img.data('alt'),
            'legend': img.data('legend'),
            'src' : img.attr('src')
        });
        this.hide();
    }
}

export default MediaModalView;
