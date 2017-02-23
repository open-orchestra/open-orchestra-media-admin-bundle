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
    constructor (options) {
        super(options);
        let noFilter =  {'': 'open_orchestra_media_admin.media_filter.none'};
        let mediaTypes = Application.getConfiguration().getParameter('media_filter_type');
        this.mediaTypes = Object.assign(noFilter, mediaTypes);
        this._filterType = '';
        if (!_.isUndefined(options.filterType)) {
            this._filterType = options.filterType;
        }
        this._selectionMod = options.selectionMod;
    }

    /**
     * Render medias view
     */
    render() {
            new FoldersTree().fetch({
                siteId: Application.getContext().siteId,
                success: (foldersTree) => {
                    let template = this._renderTemplate('Media/mediasView', {
                        language    : Application.getContext().language,
                        types       : this.mediaTypes,
                        foldersTree : foldersTree,
                        selectionMod: this._selectionMod,
                        filterType  : this._filterType
                    });
                    this.$el.html(template);
                    this._listView = new MediaListView({
                        collection  : this._collection,
                        settings    : this._settings,
                        filterType  : this._filterType,
                        selectionMod: this._selectionMod
                    });
                    $('.medias', this.$el).html(this._listView.render().$el);
                }
            });

            return this;
    }
}

export default MediasView;
