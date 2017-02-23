import MediaRouter         from './Router/Media/MediaRouter'
import FormBehaviorManager from '../Service/Form/Behavior/Manager'
import MediaChoice         from '../Service/Form/Behavior/MediaChoice'
import Application         from '../Application/Application'

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
