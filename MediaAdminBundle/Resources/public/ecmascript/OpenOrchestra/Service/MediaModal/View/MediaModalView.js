import ModalView     from '../../../Service/Modal/View/ModalView'
import Medias        from '../../../Application/Collection/Media/Medias'
import Application   from '../../../Application/Application'
import MediasView    from '../../../Application/View/Media/MediasView'
import DateFormatter from '../../../Service/DataFormatter/DateFormatter'

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
        this._filterType = options.filterType;
        this.events['click #modal-media-choose']  = '_previewMedia';
        this.events['change #modal-media-format'] = '_updatePreview';
        this.events['click #modal-media-select']  = '_selectMedia';
        this.events['click #modal-media-return']  = 'render';
    }

    /**
     * @return {Object}
     */
    render() {
        let page = 0;
        let pageLength = 10;
        let container = this._createModalContainer(Translator.trans('open_orchestra_media_admin.select.choose'));
        new Medias().fetch({
            data   : {
                'start'       : page * pageLength,
                'length'      : pageLength,
                'filter[type]': this._filterType
            },
            success: (medias) => {
                let mediasView = new MediasView({
                    filterType: this._filterType,
                    collection: medias,
                    settings  : {
                        page        : page,
                        deferLoading: [medias.recordsTotal, medias.recordsFiltered],
                        data        : medias.models,
                        pageLength  : pageLength,
                    },
                    selectionMod: true
                });
                this._mediaCollection = medias;
                let el = mediasView.render().$el;
                $('.modal-body', container).append(el);
                this.$el.html(container);
            }
        });

        return this;
    }

    /**
     * Create the modal
     *
     * @param {String}  title
     * @param {Boolean} withFooter
     *
     * @return {Object}
     */
    _createModalContainer(title, withFooter = false) {
        let template = this._renderTemplate('Media/Modal/mediaModalContainer', {
            'title': title,
            'withFooter': withFooter
        });

        return $(template);
    }

    /**
     * Open the media details screen
     *
     * @param {Object} event
     */
    _previewMedia(event)
    {
        let mediaList = this._mediaCollection.where({id: $(event.target).data('id') });
        let media = mediaList[0];
        let container = this._createModalContainer(media.get('title'), true);
        let template = 'Media/Modal/mediaBaseDetailView';
        let mediaViewTemplates = Application.getConfiguration().getParameter('mediaViewTemplates');
        if (typeof mediaViewTemplates[media.get('media_type')] !== 'undefined') {
            template = mediaViewTemplates[media.get('media_type')];
        }
        $('.modal-body', container).append(this._renderTemplate(template, {media: media, DateFormatter: DateFormatter}));
        $('.modal-footer', container).append(this._renderTemplate('Media/Modal/Include/buttons'));
        this.$el.html(container);
    }

    /**
     * Update the preview
     *
     * @param {Object} event
     */
    _updatePreview(event)
    {
        $('#modal-media-img', this.$el).attr('src', $(event.currentTarget).find(':selected').data('src'));
    }

    /**
     * Select the media
     *
     * @param {Object} event
     */
    _selectMedia(event)
    {
        this._selectCallback({
            'id'    : $('#modal-media-img', this.$el).data('id'),
            'format': $('#modal-media-format', this.$el).val(),
            'alt'   : $('#modal-media-alt', this.$el).val(),
            'legend': $('#modal-media-legend', this.$el).val(),
            'src'   : $('#modal-media-img', this.$el).attr('src')
        });
        this.hide();
    }
}

export default MediaModalView;
