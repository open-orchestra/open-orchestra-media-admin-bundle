import AbstractCollectionView from '../../../Service/DataTable/View/AbstractCollectionView'
import MediaListView          from '../../View/Media/MediaListView'
import Application            from '../../Application'
import FoldersTree            from '../../Collection/Folder/FoldersTree'

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
        new FoldersTree().fetch({
            siteId: this._siteId,
            success: (foldersTree) => {
                let template = this._renderTemplate('Media/mediasView', {
                    language    : Application.getContext().language,
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

        return this;
    }
}

export default MediasView;
