import MediaRouter from './Router/Media/MediaRouter'

/**
 * @class MediaSubApplication
 */
class MediaSubApplication
{
    /**
     * Run sub Application
     */
    run() {
        this._initRouter();
    }

    /**
     * Initialize router
     * @private
     */
    _initRouter() {
        new MediaRouter();
    }
}

export default (new MediaSubApplication);
