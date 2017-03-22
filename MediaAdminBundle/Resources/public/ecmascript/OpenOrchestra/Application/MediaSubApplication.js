import MediaRouter         from './Router/Media/MediaRouter'
import FolderRouter        from './Router/Folder/FolderRouter'
import FormBehaviorManager from '../Service/Form/Behavior/Manager'
import MediaChoice         from '../Service/Form/Behavior/MediaChoice'
import Application         from '../Application/Application'
import TinymceManager      from '../Service/Tinymce/TinymceManager'

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

        var user_roles = Application.getContext().user.roles;
        var is_admin = user_roles.indexOf('ROLE_DEVELOPER') > -1 || user_roles.indexOf('ROLE_DEVELOPER') > -1;
        Application.getContext().updateUserAccessSection(
            'media',
            is_admin || user_roles.indexOf('EDITORIAL_MEDIA_CONTRIBUTOR') > -1
        );
        Application.getContext().updateUserAccessSection(
            'media_folder',
            is_admin || user_roles.indexOf('EDITORIAL_MEDIA_FOLDER_CONTRIBUTOR') > -1
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
