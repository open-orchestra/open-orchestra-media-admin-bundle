import MediaFormView    from 'OpenOrchestra/Application/View/Media/MediaFormView'

/**
 * @class MediaImageFormView
 */
class MediaImageFormView extends MediaFormView
{
    /**
     * Pre initialize
     */
    preinitialize(options) {
        super.preinitialize();

        this.events['change select#oo_media_image_format'] = '_selectAlternative';
        this.events['click a#crop_action_button']          = '_setupCrop';
        this.events['click a#upload_action_button']        = '_setupOverride';

        this._template = 'Media/mediaImageFormView';
        this._cropParam = [];
    }

    /**
     * @inheritdoc
     */
    render() {
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
     * Triggered when the template is rendered
     * @private
     */
    _onRenderEnd() {
        this._hideOverrideTool();
    }

    /**
     * Triggered when the user has selected an alternative format
     * @private
     */
    _selectAlternative(event) {
        this._hideCropTool();
        this._hideOverrideTool();
        this._hideAlternatives();
        this._destroyCropApi();
        this._showPreview($('#oo_media_image_format', this.$el).val());
    }

    /**
     * Show the alternative preview for format
     *
     * @param {String} format
     *
     * @private
     */
    _showPreview(format) {
        $('#preview-pane .preview-container', this.$el).height($('.media_crop_' + format, this.$el).height());
        $('#preview-pane .preview-container', this.$el).width($('.media_crop_' + format, this.$el).width());

        if (format != '') {
            $('.media_crop_' + format, this.$el).show();
            $('.media_format_actions', this.$el).show();
        } else {
            $('.media_crop_original', this.$el).show();
            $('.media_format_actions', this.$el).hide();
        }
    }

    /**
     * Setup the crop tool
     * @private
     */
    _setupCrop(event) {
        event.preventDefault();

        this._destroyCropApi();
        this._hideOverrideTool();
        this._showCropTool();

        this._cropParam['$preview'] = $('#preview-pane', this.$el);
        this._cropParam['$pimg'] = $('#preview-pane .preview-container img', this.$el);
        this._cropParam['$pcnt'] = $('#preview-pane .preview-container', this.$el);
        this._cropParam['xsize'] = this._cropParam['$pcnt'].width();
        this._cropParam['ysize'] = this._cropParam['$pcnt'].height();

        let naturalImage = new Image();
        naturalImage.src = $('img.superbox-current-img', this.$el).attr('src');
        let naturalWidth = naturalImage.width;
        let naturalHeight = naturalImage.height;
        naturalImage.remove();

        let viewContext = this;
        $('.superbox-current-img', this.$el).Jcrop(
            {
                'onSelect': this._updateCoords,
                'boxWidth': 600,
                'trueSize': [naturalWidth, naturalHeight]
            },
            function () {
                let bounds = this.getBounds();
                viewContext._cropParam['boundx'] = bounds[0];
                viewContext._cropParam['boundy'] = bounds[1];
                viewContext._cropParam['jcrop_api'] = this;
                viewContext._cropParam['$preview'].appendTo(this.ui.holder);

                return;
            }
        );

        return this;
    }

    /**
     * setup the override tool
     * @private
     */
    _setupOverride(event) {
        event.preventDefault();
        this._hideCropTool();
        this._showOverrideTool();
    }

    /**
     * destroy the crop Api
     * @private
     */
    _destroyCropApi() {
        if (this._cropParam['jcrop_api']) {
            this._cropParam['jcrop_api'].destroy();
        }
    }

    /**
     * Update crop coordinates
     *
     * @param  {Object} selectedBox
     *
     * @private
     */
    _updateCoords(selectedBox) {
        $('#oo_media_image_x', this.$el).val(selectedBox.x);
        $('#oo_media_image_y', this.$el).val(selectedBox.y);
        $('#oo_media_image_w', this.$el).val(selectedBox.w);
        $('#oo_media_image_h', this.$el).val(selectedBox.h);
    }

    /**
     * Show crop tool
     *
     * @private
     */
    _showCropTool() {
        $('#crop-group', this.$el).show();
    }

    /**
     * Hide crop tool
     *
     * @private
     */
    _hideCropTool() {
        $('#crop-group', this.$el).hide();
    }

    /**
     * Show override tool
     *
     * @private
     */
    _showOverrideTool() {
        $('#oo_media_image_file', this.$el).closest('.form-group').show();
    }

    /**
     * Hide override tool
     *
     * @private
     */
    _hideOverrideTool() {
        $('#oo_media_image_file', this.$el).closest('.form-group').hide();
    }

    /**
     * Hide alternatives
     *
     * @private
     */
    _hideAlternatives() {
        $('.media_crop_preview img', this.$el).hide();
    }
}

export default MediaImageFormView;
