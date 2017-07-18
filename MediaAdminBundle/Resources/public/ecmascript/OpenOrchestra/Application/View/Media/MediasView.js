import AbstractCollectionView from 'OpenOrchestra/Service/DataTable/View/AbstractCollectionView'
import MediaListView          from 'OpenOrchestra/Application/View/Media/MediaListView'
import Application            from 'OpenOrchestra/Application/Application'
import FoldersTree            from 'OpenOrchestra/Application/Collection/Folder/FoldersTree'

/**
 * @class MediasView
 */
class MediasView extends AbstractCollectionView
{
    /**
     * Constructor
     */
    constructor ({siteId, filterType, collection, settings, selectionMod}) {
        super({collection: collection, settings: settings});
        let noFilter =  {'': 'open_orchestra_media_admin.media_filter.none'};
        let mediaTypes = Application.getConfiguration().getParameter('media_filter_type');
        this.mediaTypes = Object.assign(noFilter, mediaTypes);
        this._filterType = '';
        if (!_.isUndefined(filterType)) {
            this._filterType = filterType;
        }
        this._selectionMod = selectionMod;
        this._siteId = siteId;
    }

    /**
     * Render medias view
     */
    render() {
        if (0 === this._collection.recordsTotal) {
            this._renderEmptyList();
        } else {
            this._renderList();
        }

        return this;
    }

    /**
     * Render empty list
     */
    _renderEmptyList() {
        let params = {};

        if (!this._selectionMod) {
            params = {
                title : Translator.trans('open_orchestra_media_admin.media.title_list'),
                urlAdd: Backbone.history.generateUrl('newMedia')
            }
        }
        let template = this._renderTemplate('List/emptyListView' , params);
        this.$el.html(template);
    }

    /**
     * Render list
     */
    _renderList() {
        new FoldersTree().fetch({
            siteId: this._siteId,
            success: (foldersTree) => {
                let template = this._renderTemplate('Media/mediasView', {
                    language    : Application.getContext().get('language'),
                    types       : this.mediaTypes,
                    foldersTree : foldersTree,
                    selectionMod: this._selectionMod,
                    filterType  : this._filterType,
                    can_create  : this._collection.rights.can_create
                });
                this.$el.html(template);

                this._listView = new MediaListView({
                    collection  : this._collection,
                    settings    : this._settings,
                    filterType  : this._filterType,
                    selectionMod: this._selectionMod,
                    siteId      : this._siteId
                });
                $('.medias', this.$el).html(this._listView.render().$el);
            }
        });
    }
}

export default MediasView;
