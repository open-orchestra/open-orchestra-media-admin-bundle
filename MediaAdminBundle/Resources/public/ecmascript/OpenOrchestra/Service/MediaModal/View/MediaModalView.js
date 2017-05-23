import ModalView              from '../../../Service/Modal/View/ModalView'
import Medias                 from '../../../Application/Collection/Media/Medias'
import SitesShareMediaLibrary from '../../../Application/Collection/Site/SitesShareMediaLibrary'
import Application            from '../../../Application/Application'
import MediasView             from '../../../Application/View/Media/MediasView'
import MediaUploadView        from '../../../Application/View/Media/MediaUploadView'
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
        this._pageLength = 10;

        this.events['click #modal-media-choose']  = '_previewMedia';
        this.events['change #modal-media-format'] = '_updatePreview';
        this.events['click #modal-media-select']  = '_selectMedia';
        this.events['click #modal-media-return']  = '_renderMedias';
        this.events['change .select-site']        = '_changeSite';
        this.events['click .upload-popup-mode']   = '_uploadPopupMode';

    }

    /**
     * @return {Object}
     */
    render() {
        new SitesShareMediaLibrary().fetch({
            success: (sites) => {
                new Medias().fetch({
                    urlParameter: {
                        siteId: this._currentSiteId
                    },
                    apiContext: 'withoutPerimeter',
                    data: {
                        'start': 0,
                        'length': this._pageLength,
                        'filter[type]': this._filterType
                    },
                    success: (medias) => {
                        let template = this._renderTemplate('Media/Modal/mediaModalContainer', {
                            sites: sites.models,
                            currentSiteId: this._currentSiteId,
                            can_create: medias.rights.can_create
                        });

                        this.$el.html(template);
                        this._renderMedias({medias : medias});
                    }
                })
            }
        });

        return this;
    }

    /**
     * @private
     */
    _renderMedias({medias} = {}) {
        this._displayLoader($('.modal-body', this.$el));
        if (typeof medias == 'undefined') {
            new Medias().fetch({
                urlParameter: {
                    siteId: this._currentSiteId
                },
                apiContext: 'withoutPerimeter',
                data   : {
                    'start': 0,
                    'length': this._pageLength,
                    'filter[type]': this._filterType
                },
                success: (medias) => {
                    this._renderMediasAfterLoad({medias: medias})
                }
            });
        } else {
            this._renderMediasAfterLoad({medias: medias})
        }
    }

    _renderMediasAfterLoad({medias}) {
        let mediasView = new MediasView({
            siteId: this._currentSiteId,
            filterType: this._filterType,
            collection: medias,
            settings  : {
                page        : 0,
                deferLoading: [medias.recordsTotal, medias.recordsFiltered],
                data        : medias.models,
                pageLength  : this._pageLength
            },
            selectionMod: true
        });
        this._mediaCollection = medias;
        let el = mediasView.render().$el;

        $('.modal-title', this.$el).html(Translator.trans('open_orchestra_media_admin.select.choose'));
        $('.modal-body', this.$el).html(el);
        $('.modal-footer', this.$el).html('').hide();
    }

    /**
     * Open the media details screen
     *
     * @param {Object} event
     */
    _previewMedia(event)
    {
        let mediaList = this._mediaCollection.where({id: $(event.target).data('id') });
        this._createPreviewMedia(mediaList[0]);
    }

    /**
     * Open the media details screen
     *
     * @param {Object} media
     */
    _createPreviewMedia(media) {
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

        $('.modal-header .upload-popup-mode', this.$el).hide();
        if (this._currentSiteId == Application.getContext().siteId) {
            $('.modal-header .upload-popup-mode', this.$el).show();
        }

        this._renderMedias();
    }

    /**
     * @param {Object} event
     * @private
     */
    _uploadPopupMode(event){
        let mediaUploadView = new MediaUploadView({mode : 'popup'});
        this.listenTo(mediaUploadView, 'modal-media-return', $.proxy(this._renderMedias, this));
        $('.modal-body', this.$el).html(mediaUploadView.render().$el);
        $('.modal-footer', this.$el).html(mediaUploadView.mediaUploadActionView.render().$el).show();
        Backbone.Events.on('media:uploaded', $.proxy(this._createPreviewMedia, this));
    }
}

export default MediaModalView;
