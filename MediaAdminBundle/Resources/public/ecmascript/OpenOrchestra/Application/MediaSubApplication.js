import MediaRouter         from 'OpenOrchestra/Application/Router/Media/MediaRouter'
import FolderRouter        from 'OpenOrchestra/Application/Router/Folder/FolderRouter'
import FormBehaviorManager from 'OpenOrchestra/Service/Form/Behavior/Manager'
import MediaChoice         from 'OpenOrchestra/Service/Form/Behavior/MediaChoice'
import Application         from 'OpenOrchestra/Application/Application'
import TinymceManager      from 'OpenOrchestra/Service/Tinymce/TinymceManager'

/**
 * @class MediaSubApplication
 */
class MediaSubApplication
{
    /**
     * Run sub Application
     */
    run() {
        this._initConfiguration();
        this._initRouter();
        this._initFormBehaviorManager();
    }

    /**
     * Initialize configuration
     * @private
     */
    _initConfiguration() {
        Application.getConfiguration().addParameter('mediaViewTemplates', {'image': 'Media/Modal/mediaImageDetailView'});
        let user = Application.getContext().get('user');
        let roles = user.roles;
        let is_admin = roles.indexOf('ROLE_DEVELOPER') > -1 || roles.indexOf('ROLE_PLATFORM_ADMIN') > -1;
        Application.getContext().updateUserAccessSection(
            'media',
            is_admin || roles.indexOf('EDITORIAL_MEDIA_CONTRIBUTOR') > -1
        );
        Application.getContext().updateUserAccessSection(
            'media_folder',
            is_admin || roles.indexOf('EDITORIAL_MEDIA_FOLDER_CONTRIBUTOR') > -1
        );

        let toolbar = TinymceManager.getSetting('toolbar');
        TinymceManager.setSetting('toolbar', toolbar + ' | media');
    }

    /**
     * Initialize router
     * @private
     */
    _initRouter() {
        new MediaRouter();
        new FolderRouter();
    }

    /**
     * Initialize form behavior library
     * @private
     */
    _initFormBehaviorManager() {
        FormBehaviorManager.add(MediaChoice);
    }
}

export default (new MediaSubApplication);
