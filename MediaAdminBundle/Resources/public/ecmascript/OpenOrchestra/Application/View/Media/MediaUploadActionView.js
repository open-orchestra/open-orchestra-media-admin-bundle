import OrchestraView from '../OrchestraView'

/**
 * @class MediaUploadActionView
 */
class MediaUploadActionView extends OrchestraView {
    /**
     * @inheritdoc
     */
    preinitialize() {
        this.events = {
            'click .submit-upload'     : '_submitUpload',
            'click .cancel-upload'     : '_resetUpload',
            'click .delete-element'    : '_deleteElement',
            'click .modal-media-return': '_renderMedias'
        }
        this.className = 'modal-footer-button';
    }

    /**
     * Initialize
     *
     * @param {String} mode
     */
    initialize({mode} = {mode: 'library'}) {
        this._mode = mode;
    }
        /**
     * @inheritdoc
     */
    render() {
        let template = this._renderTemplate('Media/uploadActionView', {
            mode: this._mode
        });
        this.$el.html(template);
        this.hide();

        return this;
    }

    show() {
        $('button.optionnal', this.$el).show();
    }

    hide() {
        $('button.optionnal', this.$el).hide();
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

    /**
     * @private
     */
    _renderMedias() {
        $('button', this.$el).hide();
        this.trigger('modal-media-return');
    }
}
export default MediaUploadActionView;
