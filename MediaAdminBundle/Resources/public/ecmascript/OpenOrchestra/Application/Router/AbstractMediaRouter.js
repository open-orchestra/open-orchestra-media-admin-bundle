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
                    link: '#'+Backbone.history.generateUrl('listMedia'),
                    id : 'course-media'
                },
                {
                    label: Translator.trans('open_orchestra_media_admin.folder.title_list'),
                    link: '#'+Backbone.history.generateUrl('listFolders'),
                    id : 'course-folder'
                }
            ]
        ]
    }
}

export default AbstractMediaRouter;
