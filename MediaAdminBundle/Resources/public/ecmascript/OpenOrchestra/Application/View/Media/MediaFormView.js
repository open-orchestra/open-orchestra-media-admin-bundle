import AbstractFormView     from '../../../Service/Form/View/AbstractFormView'
import Application          from '../../Application'
import Media                from '../../Model/Media/Media'
import FormViewButtonsMixin from '../../../Service/Form/Mixin/FormViewButtonsMixin'
import ApplicationError     from '../../../Service/Error/ApplicationError'
import FlashMessageBag      from '../../../Service/FlashMessage/FlashMessageBag'
import FlashMessage         from '../../../Service/FlashMessage/FlashMessage'

/**
 * @class MediaFormView
 */
class MediaFormView extends mix(AbstractFormView).with(FormViewButtonsMixin)
{
    /**
     * Pre initialize
     */
    preinitialize(options) {
        super.preinitialize();

        this._template = 'Media/mediaFormView';
    }

    /**
     * Initialize
     * @param {Form}   form
     * @param {String} mediaId
     */
    initialize({form, mediaId = null}) {
        super.initialize({form : form});
        this._mediaId = mediaId;
    }

    /**
     * @inheritdoc
     */
    render() {
        let template = this._renderTemplate(this._template);
        this.$el.html(template);
        this._$formRegion = $('.form-edit', this.$el);
        super.render();
        this._onRenderEnd();

        return this;
    }

    /**
     * Refresh render
     */
    refreshRender() {
        super.refreshRender();
        this._onRenderEnd();
    }

    /**
     * hook on render end
     *
     * @private
     */
    _onRenderEnd() {
    }
}

export default MediaFormView;
