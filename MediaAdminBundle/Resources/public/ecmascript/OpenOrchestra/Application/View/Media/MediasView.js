import AbstractCollectionView from '../../../Service/DataTable/View/AbstractCollectionView'
import MediaListView          from '../../View/Media/MediaListView'
import Application            from '../../Application'
import Folders                from '../../Collection/Folder/Folders'

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
        this.mediaTypes = {
            ''       : 'open_orchestra_media_admin.media_filter.none',
            'default': 'open_orchestra_media_admin.media_filter.default',
            'image'  : 'open_orchestra_media_admin.media_filter.image',
            'audio'  : 'open_orchestra_media_admin.media_filter.audio',
            'video'  : 'open_orchestra_media_admin.media_filter.video',
            'pdf'    : 'open_orchestra_media_admin.media_filter.pdf'
        };
    }

    /**
     * Render medias view
     */
    render() {
        if (0 === this._collection.recordsTotal) {
            let template = this._renderTemplate('List/emptyListView' , {
                title: Translator.trans('open_orchestra_media_admin.media.title_list'),
                urlAdd: ''
            });
            this.$el.html(template);

            return this;
        } else {
            let folders = new Folders();
            $.when(
                folders.fetch({siteId: Application.getContext().siteId})
            ).done( () => {
                let template = this._renderTemplate('Media/mediasView', {
                    language: Application.getContext().language,
                    types   : this.mediaTypes,
                    folders : folders
                });
                this.$el.html(template);
                this._listView = new MediaListView({
                    collection: this._collection,
                    settings  : this._settings
                });
                $('.medias-list', this.$el).html(this._listView.render().$el);
            });

            return this;
        }
    }
}

export default MediasView;
