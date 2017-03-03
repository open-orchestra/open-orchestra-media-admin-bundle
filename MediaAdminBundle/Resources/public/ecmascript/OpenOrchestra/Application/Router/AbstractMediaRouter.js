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
                label:Translator.trans('open_orchestra_backoffice.navigation.contribution.title')
            },
            {
                label: Translator.trans('open_orchestra_media_admin.navigation.contribution.media')
            },
            [
                {
                    label: Translator.trans('open_orchestra_media_admin.media.title_list'),
                    link: '#'+Backbone.history.generateUrl('listMedia')
                },
                {
                    label: Translator.trans('open_orchestra_media_admin.folder.title_list'),
                    link: '#'+Backbone.history.generateUrl('listFolders')
                }
            ]
        ]
    }
}

export default AbstractMediaRouter;
