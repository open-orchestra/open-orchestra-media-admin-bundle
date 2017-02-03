import AbstractDataTableView       from '../../../Service/DataTable/View/AbstractDataTableView'
import UrlPaginateViewMixin        from '../../../Service/DataTable/Mixin/UrlPaginateViewMixin'
import DeleteCheckboxListViewMixin from '../../../Service/DataTable/Mixin/DeleteCheckboxListViewMixin'

/**
 * @class MediaListView
 */
class MediaListView extends mix(AbstractDataTableView).with(UrlPaginateViewMixin, DeleteCheckboxListViewMixin)
{
    /**
     * @inheritDoc
     */
    _drawCallback(settings)
    {
        let mediaList = $('<div></div>').addClass('well');
        var context = this;

        this._collection.each(function(media) {
            let template = context._renderTemplate('Media/mediaListCellView', {
                media: media
            });
            mediaList.append(template);
            $('input#checkbox' + media.cid + '.delete-checkbox', mediaList).data(media);
        });

        $(".table-responsive", this.$el).html(mediaList);
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
}

export default MediaListView;
