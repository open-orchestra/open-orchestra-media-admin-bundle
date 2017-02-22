import AbstractDataTableView       from '../../../Service/DataTable/View/AbstractDataTableView'
import UrlPaginateViewMixin        from '../../../Service/DataTable/Mixin/UrlPaginateViewMixin'
import DeleteCheckboxListViewMixin from '../../../Service/DataTable/Mixin/DeleteCheckboxListViewMixin'

/**
 * @class MediaListView
 */
class MediaListView extends mix(AbstractDataTableView).with(UrlPaginateViewMixin, DeleteCheckboxListViewMixin)
{
    /**
     * Constructor
     *
     * @param {Object} options
     */
    constructor(options) {
        options.settings.initComplete = () => {
            this._interval = setInterval($.proxy(this._refreshList, this), 5000);
        };
        super(options);
        this._filterType = options.filterType;
        this._selectionMod = options.selectionMod;
        this._maxRefresh = 5;
        this._countRefresh = 0;
    }

    /**
     * @inheritDoc
     */
    _drawCallback(settings)
    {
        let mediaList = $('<div></div>').addClass('well');
        var context = this;
        this._collection.each(function(media) {
            let templateFile = 'Media/mediaListCellView';
            if (context._selectionMod) {
                templateFile = 'Media/Modal/mediaSelectCellView';
            }
            let template = context._renderTemplate(templateFile, {
                media: media
            });
            mediaList.append(template);
            $('input#checkbox' + media.cid + '.delete-checkbox', mediaList).data(media);
        });

        $(".table-responsive", this.$el).html(mediaList);

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
            this.$table.DataTable().ajax.reload();
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
    getColumnsDefinition() {
        return [];
    }

    /**
     * @inheritDoc
     */
    generateUrlUpdatePage(page) {
       return Backbone.history.generateUrl('listMedia', {page : page});
    }

    /**
     * Return options used to fetch collection
     *
     * @returns {{}}
     * @private
     */
    _getSyncOptions() {
        if ('' != this._filterType) {
            return  {data: {'filter[type]': this._filterType}};
        }

        return {};
    }
}

export default MediaListView;
