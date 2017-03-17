import OrchestraRouter from './OrchestraRouter'

/**
 * @class AbstractMediaRouter
 */
class AbstractMediaRouter extends OrchestraRouter
{
    /**
     * @inheritdoc
     */
    getBreadcrumb() {
        return [
            {
                label:Translator.trans('open_orchestra_backoffice.menu.contribution.title')
            },
            {
                label: Translator.trans('open_orchestra_media_admin.menu.contribution.media')
            },
            [
                {
                    label: Translator.trans('open_orchestra_media_admin.media.title_list'),
                    link: '#'+Backbone.history.generateUrl('listMedia'),
                    id : 'navigation-media'
                },
                {
                    label: Translator.trans('open_orchestra_media_admin.folder.title_list'),
                    link: '#'+Backbone.history.generateUrl('listFolders'),
                    id : 'navigation-folder'
                }
            ]
        ]
    }
}

export default AbstractMediaRouter;
