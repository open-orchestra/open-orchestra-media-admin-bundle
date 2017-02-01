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
        let template = this._renderTemplate('Media/mediaListView', {
            medias: this._collection
        });
        $(".table-responsive", this.$el).html(template);
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
