import AbstractDataTableView       from '../../../Service/DataTable/View/AbstractDataTableView'
import UrlPaginateViewMixin        from '../../../Service/DataTable/Mixin/UrlPaginateViewMixin'
import DeleteCheckboxListViewMixin from '../../../Service/DataTable/Mixin/DeleteCheckboxListViewMixin'

/**
 * @class MediaListView
 */
class MediaListView extends mix(AbstractDataTableView).with(UrlPaginateViewMixin, DeleteCheckboxListViewMixin)
{
    /**
     * @param {Object} options
     */
    preinitialize(options) {
        super.preinitialize(options);
        this.events = this.events || {};
        this.events['click .order-value'] = '_changeOrder';
    }

    /**
     * @param {Object} collection
     * @param {Array}  settings
     */
    initialize({collection, settings}) {
        super.initialize({collection, settings});
        this._settings.dom = "<'header-results clearfix' <'nb-results pull-left' i>l B <'header-results-order'>p>" +
            "<'table-responsive'tr>" +
            "p";
    }

    /**
     * Constructor
     *
     * @param {Object} options
     */
    constructor({filterType, selectionMod, siteId, collection, settings}) {
        settings.initComplete = () => {
            this._interval = setInterval($.proxy(this._refreshList, this), 5000);
        };
        super({'collection': collection, 'settings': settings});
        this._filterType = filterType;
        this._selectionMod = selectionMod;
        this._siteId = siteId;
        this._maxRefresh = 5;
        this._countRefresh = 0;
    }


    /**
     * @inheritDoc
     */
    getColumnsDefinition() {
        return [
            {
                name: "updated_at",
                title: Translator.trans('open_orchestra_media_admin.table.media.updated_at'),
                orderable: true,
                orderDirection: 'desc',
            },
            {
                name: "name",
                title: Translator.trans('open_orchestra_media_admin.table.media.name'),
            },
            {
                name: "mime_type",
                title: Translator.trans('open_orchestra_media_admin.table.media.mime_type'),
            },
            {
                name: "size",
                title: Translator.trans('open_orchestra_media_admin.table.media.size'),
            }
        ];
    }


    /**
     * @inheritDoc
     */
    _drawCallback(settings)
    {
        let context = this;
        let mediaList = $('<div></div>').addClass('well').data('context', this);
        let templateFile = (context._selectionMod) ? 'Media/Modal/mediaSelectCellView' : 'Media/mediaListCellView';
        let order = typeof settings.aaSorting != 'undefined' && settings.aaSorting.length > 0 ? settings.aaSorting[0] : [undefined, undefined];

        if (context._selectionMod) {
            this._collection.each(function(media) {
                mediaList.append(context._renderTemplate(templateFile, {media: media}));
            });
        } else {
            this._collection.each(function(media) {
                let $template = $(context._renderTemplate(templateFile, {media: media}));
                context._createDeleteCheckbox.apply(mediaList, [$('.delete-button', $template), null, media]);
                mediaList.append($template);
            });
        }
        $(".table-responsive", this.$el).html(mediaList);
        $(".header-results-order", this.$el).html(this._renderTemplate('Media/mediaOrderView', {
            configuration: this.getColumnsDefinition(),
            order: order
        }));
    }

    /**
     * @private
     */
    _refreshList()
    {
        let mediasNoThumbnail = this.collection.filter(function(model){
            return model.has('thumbnail') === false;
        });

        if (
            0 === mediasNoThumbnail.length || 
            false === this.$el.is(":visible") || 
            this._countRefresh >= this._maxRefresh
        ) {
            clearInterval(this._interval);
        } else {
            this._countRefresh++;
            this.$table.DataTable(this._settings).ajax.reload();
        }
    }

    /**
     * @inheritDoc
     */
    getTableId() {
        return 'media_list';
    }

    /**
     * @inheritDoc
     */
    generateUrlUpdatePage(page) {
       return Backbone.history.generateUrl('listMedia', {page : page});
    }

    /**
     * @param {Object} event
     * @private
     */
    _changeOrder(event){
        let $target = $(event.target);
        let index = this.$table.DataTable().column($(event.target).data('name') + ':name').index();
        let order = $target.hasClass('asc') ? 'desc' : 'asc';
        $('option', $target.parent()).removeClass('desc').removeClass('asc');
        $target.addClass(order);
        this.$table.DataTable().order([index, order]).draw();
    }

    /**
     * Return options used to fetch collection
     *
     * @returns {{}}
     * @private
     */
    _getSyncOptions() {
        let syncOptions = {
            urlParameter: {
                siteId: this._siteId
            }
        };

        if (this._selectionMod) {
            syncOptions.apiContext = 'withoutPerimeter';
        }

        if ('' != this._filterType) {
            syncOptions.data = {'filter[type]': this._filterType};
        }

        return syncOptions;
    }
}

export default MediaListView;
