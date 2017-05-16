import OrchestraView from '../OrchestraView'

/**
 * @class MediaFormView
 */
class MediaUploadActionView extends OrchestraView {
    /**
     * @inheritdoc
     */
    preinitialize() {
        this.events = {
            'click .submit-upload': '_submitUpload',
            'click .cancel-upload': '_resetUpload',
            'click .delete-element': '_deleteElement'
        }
    }

    /**
     * @inheritdoc
     */
    render() {
        let template = this._renderTemplate('Media/uploadActionView');
        this.$el.html(template);
        this.hide();

        return this;
    }

    show() {
        this.$el.show();
    }

    hide() {
        this.$el.hide();
    }

    /**
     * @private
     */
    _submitUpload() {
        this.trigger('submit-upload');
    }
    /**
     * @private
     */
    _resetUpload() {
        this.trigger('cancel-upload');
    }
    /**
     * @private
     */
    _deleteElement() {
        this.trigger('delete-element');
    }
}
export default MediaUploadActionView;
