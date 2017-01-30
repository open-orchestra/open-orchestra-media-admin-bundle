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
    getTableId() {
        return 'media_list';
    }

    /**
     * @inheritDoc
     */
    getColumnsDefinition() {
        return [
            this._getColumnsDefinitionDeleteCheckbox(),
            {
                name: "title",
                title: Translator.trans('open_orchestra_media_admin.table.media.label'),
                orderable: true,
                orderDirection: 'desc',
                visibile: true,
                createdCell: this._createEditLink
            },
//            {
//                name: "properties",
//                title: Translator.trans('open_orchestra_workflow_admin.table.statuses.specificities'),
//                orderable: false,
//                visibile: true,
//                render: this._getAgglomeratedProperties
//            },
//            {
//                name: "display_color",
//                title: Translator.trans('open_orchestra_workflow_admin.table.statuses.display_color'),
//                orderable: false,
//                visibile: true,
//                render: this._getFormatedColor
//            }
        ];
    }

    /**
     * @inheritDoc
     */
    generateUrlUpdatePage(page) {
       return Backbone.history.generateUrl('listMedia', {page : page});
    }

    /**
     *
     * @param {Object} td
     * @param {Object} cellData
     * @param {Object} rowData
     * @private
     */
    _createEditLink(td, cellData, rowData) {
        let link = Backbone.history.generateUrl('editMedia', {
            mediaId: rowData.get('id'),
            name: rowData.get('name')
        });
        cellData = $('<a>',{
            text: cellData,
            href: '#'+link
        });

        $(td).html(cellData)
    }
}

export default MediaListView;
