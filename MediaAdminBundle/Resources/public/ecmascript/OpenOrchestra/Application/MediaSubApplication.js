import MediaRouter         from './Router/Media/MediaRouter'
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
        let toolbar = TinymceManager.getSetting('toolbar');
        TinymceManager.setSetting('toolbar', toolbar + ' | media');
    }

    /**
     * Initialize router
     * @private
     */
    _initRouter() {
        new MediaRouter();
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
