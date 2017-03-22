import OrchestraRouter from './OrchestraRouter'
import Application     from '../../Application/Application'

/**
 * @class AbstractMediaRouter
 */
class AbstractMediaRouter extends OrchestraRouter
{
    /**
     * @inheritdoc
     */
    getBreadcrumb() {
        var subMenuItem = [];
        if (Application.getContext().user.access_section.media) {
            subMenuItem.push({
                label: Translator.trans('open_orchestra_media_admin.media.title_list'),
                link : '#'+Backbone.history.generateUrl('listMedia'),
                id   : 'navigation-media'
            });
        }
        if (Application.getContext().user.access_section.media_folder) {
            subMenuItem.push({
                label: Translator.trans('open_orchestra_media_admin.folder.title_list'),
                link : '#'+Backbone.history.generateUrl('listFolders'),
                id   : 'navigation-folder'
            });
        }

        return [
            {
                label:Translator.trans('open_orchestra_backoffice.menu.contribution.title')
            },
            {
                label: Translator.trans('open_orchestra_media_admin.menu.contribution.media')
            },
            subMenuItem
         ]
    }
}

export default AbstractMediaRouter;
