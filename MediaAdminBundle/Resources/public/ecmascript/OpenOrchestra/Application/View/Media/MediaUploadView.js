import OrchestraView         from '../OrchestraView'
import Application           from '../../Application'
import FoldersTree           from '../../Collection/Folder/FoldersTree'
import ApplicationError      from '../../../Service/Error/ApplicationError'
import Media                 from '../../Model/Media/Media'
import MediaUploadActionView from './MediaUploadActionView'

/**
 * @class MediaUploadView
 */
class MediaUploadView extends OrchestraView
{
    /**
     * @inheritdoc
     */
    preinitialize() {
        this.events = {
            'dragenter .flow-drop' : '_dragEnter',
            'dragend .flow-drop'   : '_dragEnd',
            'drop .flow-drop'      : '_dragEnd',
        }
    }

    /**
     * Initialize
     */
    initialize(mode) {
        this._flow = new Flow({
            target     : $.proxy(this._getFlowTarget, this),
            query      : $.proxy(this._getFlowQuery, this),
            chunkSize  : 1024 * 1024,
            testChunks : false,
            singleFile : mode == 'popup'
        });
        this._allowed_mime_types = Application.getConfiguration().getParameter('allowed_mime_types');
        this._folderTree = new FoldersTree();
        this._colors = {
            upload     : '#38b5e9',
            error      : '#FF0000',
            success    : '#24bc7a',
            processing : '#FF4500'
        }
        this._mode = mode || 'library';
        this.mediaUploadActionView = new MediaUploadActionView();
        this.listenTo(this.mediaUploadActionView, 'submit-upload', $.proxy(this._submitUpload, this));
        this.listenTo(this.mediaUploadActionView, 'cancel-upload', $.proxy(this._resetUpload, this));
        this.listenTo(this.mediaUploadActionView, 'delete-element', $.proxy(this._deleteElement, this));

    }

    /**
     * Render view
     */
    render() {
        this._folderTree.fetch({
            siteId: Application.getContext().siteId,
            success: () => {
                let hasPerimeter = this._hasPerimeter(this._folderTree.models[0].get('children'));
                let template = this._renderTemplate('Media/uploadView', {
                    hasPerimeter: hasPerimeter,
                    mode: this._mode
                });
                this.$el.html(template);
                if (this._mode == 'library') {
                    this.$el.append(this.mediaUploadActionView.render().$el);
                }
                this.initFileUpload();
            }
        });

        return this;
    }

    /**
     * Check if the user can act on a folder
     */
    _hasPerimeter(foldersTree) {
        for (let i = 0; i < foldersTree.length; i++) {
            if (foldersTree[i].get('folder').get('rights').can_create_media) {
                return true;
            }

            if (this._hasPerimeter(foldersTree[i])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get url to upload
     *
     * @param {Object}  flowFile
     * @param {Object}  flowChunk
     * @param {Boolean} isTest
     */
    _getFlowTarget(flowFile, flowChunk, isTest) {
        let folderId = $('.flow-list #'+ flowFile.uniqueIdentifier +' #folderId-'+ flowFile.uniqueIdentifier, this.$el).val();
        if (null === folderId || typeof folderId == "undefined") {
            throw new ApplicationError('Invalid folderId');
        }

        return Routing.generate('open_orchestra_api_media_upload', {'folderId' : folderId});
    }

    /**
     * Get parameter added to post data
     *
     * @param {Object} flowFile
     * @param {Object} flowChunk
     *
     * @return {Object}
     */
    _getFlowQuery(flowFile, flowChunk) {
        let title = flowFile.name;
        let $inputTitle = $('.flow-list #'+ flowFile.uniqueIdentifier +' input[name="title"]', this.$el);
        if ($inputTitle.length > 0) {
            title = $inputTitle.val()
        }

        return {title : title};
    }

    /**
     * drag enter
     */
    _dragEnter() {
        $('.flow-drop', this.$el).addClass('flow-dragover');
    }

    /**
     * drag end
     */
    _dragEnd() {
        $('.flow-drop', this.$el).removeClass('flow-dragover');
    }

    /**
     * Initialize the flow component
     */
    initFileUpload() {
        if (!this._flow.support) {
            $('.flow-drop', this.$el).hide();
            $('.flow-error', this.$el).show();
            return;
        }

        $('.flow-drop', this.$el).initialize(() => {
            this._flow.assignDrop($('.flow-drop', this.$el)[0]);
        });
        $('.flow-browse-folder', this.$el).initialize(() => {
            this._flow.assignBrowse($('.flow-browse-folder', this.$el)[0], true);
        });
        $('.flow-browse', this.$el).initialize(() => {
            this._flow.assignBrowse($('.flow-browse', this.$el)[0], false, false, {accept: this._allowed_mime_types.join(',')});
        });

        this._flow.on('fileAdded', $.proxy(this._fileAdded, this));
        this._flow.on('fileSuccess', $.proxy(this._fileSuccess, this));
        this._flow.on('fileError', $.proxy(this._fileError, this));
        this._flow.on('fileProgress', $.proxy(this._fileProgress, this));
        this._flow.on('complete', () => {
            $('.flow-browse-folder, .flow-browse', this.$el).removeAttr("disabled");
            this._flow.assignDrop($('.flow-drop', this.$el)[0]);
        });
    }

    /**
     * @private
     */
    _submitUpload() {
        $('.progress', this.$el).show();
        this.mediaUploadActionView.hide();
        $('.flow-browse-folder, .flow-browse', this.$el).attr("disabled","disabled");
        this._flow.unAssignDrop($('.flow-drop', this.$el)[0]);
        this._flow.upload();
    }

    /**
     * @private
     */
    _resetUpload() {
        $('.flow-list, .progress', this.$el).hide();
        this.mediaUploadActionView.hide();
        $('.flow-list .medias', this.$el).empty();
        $('.progress-bar', this.$el).text('');
        $('.progress-bar', this.$el).css({width: 0});
        $('.flow-browse-folder, .flow-browse', this.$el).removeAttr("disabled");
        this._flow.assignDrop($('.flow-drop', this.$el)[0]);
        this._flow.cancel();
    }

    /**
     * @private
     */
    _deleteElement() {
        let $items = $('.flow-list .delete-checkbox:checked', this.$el);
        $items.each((key, item) => {
            let fileId = $(item).attr('data-file-id');
            if (typeof fileId !== "undefined") {
                let file = this._flow.getFromUniqueIdentifier(fileId);
                if (false !== file) {
                    this._flow.removeFile(file);
                }
                $(item).closest('#'+fileId).remove();
            }
        });

        if (0 === $('.flow-list .medias', this.$el).children().length) {
            this._resetUpload();
        }
    }

    /**
     * @param {FlowFile} flowFile
     * @private
     */
    _fileAdded(flowFile) {
        if (1 === this._flow.progress()) {
            this._resetUpload();
        }
        if (0 === flowFile.file.type.indexOf('image/')) {
            let reader = new FileReader();
            let viewContext = this;
            reader.onload = (e) => {
                $.proxy(viewContext._renderUploadPreview(flowFile, e.target.result), viewContext);
            };
            reader.readAsDataURL(flowFile.file);
        } else {
            this._renderUploadPreview(flowFile);
        }
        $('.flow-list', this.$el).show();
        this.mediaUploadActionView.show();
    }

    /**
     *
     * @param {FlowFile} flowFile
     * @param {string|null}   src
     * @private
     */
    _renderUploadPreview(flowFile, src = null) {
        let template = this._renderTemplate('Media/uploadPreview', {
            src: src,
            id: flowFile.uniqueIdentifier,
            name: flowFile.name,
            foldersTree: this._folderTree
        });
        $('.flow-list .medias', this.$el).append(template);
    }

    /**
     *
     * @param {FlowFile} flowFile
     * @param {String}   message
     * @param {Object}   chunk
     * @private
     */
    _fileSuccess(flowFile, message, chunk) {
        let response = JSON.parse(chunk.xhr.response);
        let media = new Media(response);
        let template = this._renderTemplate('Media/uploadPreviewSuccess', {
            media: media
        });
        $('.medias > #' + flowFile.uniqueIdentifier + ' .title', this.$el).replaceWith(template);
        $('.medias > #' + flowFile.uniqueIdentifier + ' .title .upload-information', this.$el).css({color: this._colors.success});
        Backbone.Events.trigger('media:uploaded', new Media(JSON.parse(message)));
    }

    /**
     * @param {FlowFile} flowFile
     * @param {String}   message
     * @param {Object}   chunk
     * @private
     */
    _fileError(flowFile, message, chunk) {
        if (500 == chunk.xhr.status) {
            message = Translator.trans('open_orchestra_media_admin.upload.server_error');
        }
        this._updateInfoFileUpload(flowFile.uniqueIdentifier, Translator.trans('open_orchestra_media_admin.upload.failed') + message, this._colors.error);
    }

    /**
     * @param {FlowFile} flowFile
     * @private
     */
    _fileProgress(flowFile) {
        let progress = '';
        let colorCode = this._colors.upload;
        if (flowFile.progress() < 1) {
            progress = Math.floor(flowFile.progress() * 100) + '% '
                + this._readablizeBytes(flowFile.averageSpeed) + '/s '
                + this._secondsToStr(flowFile.timeRemaining()) + ' '
                + Translator.trans('open_orchestra_media_admin.upload.remaining');
        } else {
            progress = Translator.trans('open_orchestra_media_admin.upload.processing');
            colorCode = this._colors.processing;
        }
        $('.flow-list .item .form-control').attr('disabled','disabled');
        this._updateInfoFileUpload(flowFile.uniqueIdentifier, progress, colorCode);

        let progressTotal = Math.floor(this._flow.progress(true) * 100) + '%';
        $('.progress-bar', this.$el).text(progressTotal);
        $('.progress-bar', this.$el).css({width: progressTotal});
    }

    /**
     * @param {String} fileId
     * @param {String} text
     * @param {String} colorCode
     * @private
     */
    _updateInfoFileUpload(fileId, text, colorCode) {
        $('#' + fileId + ' .upload-information', this.$el).text(text);
        $('#' + fileId + ' .upload-information', this.$el).css({color: colorCode});
    }

    /**
     * @param {int} bytes
     *
     * @return {String}
     */
    _readablizeBytes(bytes) {
        let size = [
            'bytes',
            'kB',
            'MB',
            'GB',
            'TB',
            'PB'
        ];
        let e = Math.floor(Math.log(bytes) / Math.log(1024));

        return (bytes / Math.pow(1024, e)).toFixed(2) + ' ' + size[e];
    }

    /**
     * @param {int} time
     *
     * @return {String}
     */
    _secondsToStr(time) {
        let years = Math.floor(time / 31536000);

        if (years) {
            return years + ' year' + this._numberEnding(years);
        }

        let days = Math.floor((time %= 31536000) / 86400);
        if (days) {
            return days + ' day' + this._numberEnding(days);
        }

        let hours = Math.floor((time %= 86400) / 3600);
        if (hours) {
            return hours + ' hour' + this._numberEnding(hours);
        }

        let minutes = Math.floor((time %= 3600) / 60);
        if (minutes) {
            return minutes + ' minute' + this._numberEnding(minutes);
        }

        let seconds = time % 60;

        return seconds + ' second' + this._numberEnding(seconds)
    }

    /**
     * @param {int} number
     *
     * @return {String}
     */
    _numberEnding(number) {
        if (number > 1) {
            return 's';
        }

        return '';
    }
}

export default MediaUploadView;
