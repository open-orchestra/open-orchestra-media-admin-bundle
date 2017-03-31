import ModalView              from '../../../Service/Modal/View/ModalView'
import Medias                 from '../../../Application/Collection/Media/Medias'
import SitesShareMediaLibrary from '../../../Application/Collection/Site/SitesShareMediaLibrary'
import Application            from '../../../Application/Application'
import MediasView             from '../../../Application/View/Media/MediasView'
import DateFormatter          from '../../../Service/DataFormatter/DateFormatter'

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
        this._currentSiteId = Application.getContext().siteId;

        this.events['click #modal-media-choose']  = '_previewMedia';
        this.events['change #modal-media-format'] = '_updatePreview';
        this.events['click #modal-media-select']  = '_selectMedia';
        this.events['click #modal-media-return']  = '_renderMedias';
        this.events['change .select-site']        = '_changeSite';
    }

    /**
     * @return {Object}
     */
    render() {
        new SitesShareMediaLibrary().fetch({
            success: (sites) => {
                let template = this._renderTemplate('Media/Modal/mediaModalContainer', {
                    'sites': sites.models,
                    'currentSiteId':  this._currentSiteId
                });

                this.$el.html(template);
                this._renderMedias();
            }
        });

        return this;
    }

    /**
     * @private
     */
    _renderMedias() {
        this._displayLoader($('.modal-body', this.$el));
        let page = 0;
        let pageLength = 10;
        new Medias().fetch({
            urlParameter: {
              siteId: this._currentSiteId
            },
            data   : {
                'start'       : page * pageLength,
                'length'      : pageLength,
                'filter[type]': this._filterType
            },
            success: (medias) => {
                let mediasView = new MediasView({
                    siteId: this._currentSiteId,
                    filterType: this._filterType,
                    collection: medias,
                    settings  : {
                        page        : page,
                        deferLoading: [medias.recordsTotal, medias.recordsFiltered],
                        data        : medias.models,
                        pageLength  : pageLength
                    },
                    selectionMod: true
                });
                this._mediaCollection = medias;
                let el = mediasView.render().$el;

                $('.modal-title', this.$el).html(Translator.trans('open_orchestra_media_admin.select.choose'));
                $('.modal-body', this.$el).html(el);
                $('.modal-footer', this.$el).html('').hide();
            }
        });
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
        let template = 'Media/Modal/mediaBaseDetailView';
        let mediaViewTemplates = Application.getConfiguration().getParameter('mediaViewTemplates');
        if (typeof mediaViewTemplates[media.get('media_type')] !== 'undefined') {
            template = mediaViewTemplates[media.get('media_type')];
        }
        template = this._renderTemplate(template, {media: media, DateFormatter: DateFormatter});

        $('.modal-title', this.$el).html(media.get('title'));
        $('.modal-body', this.$el).html(template);
        $('.modal-footer', this.$el).html(this._renderTemplate('Media/Modal/Include/buttons')).show();
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

    /**
     * @param {Object} event
     * @private
     */
    _changeSite(event)
    {
        this._currentSiteId = $(event.currentTarget).val();
        this._renderMedias();
    }
}

export default MediaModalView;
