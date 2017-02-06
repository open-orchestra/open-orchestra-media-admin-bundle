import OrchestraView from '../OrchestraView'
import Application   from '../../Application'
import FoldersTree   from '../../Collection/Folder/FoldersTree'

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
            'dragenter .flow-drop': '_dragEnter',
            'dragend .flow-drop'  : '_dragEnd',
            'drop .flow-drop'     : '_dragEnd'
        }
    }

    /**
     * Initialize
     */
    initialize() {
        this._flow = new Flow({
            'target'    : $.proxy(this._getFlowTarget, this),
            'chunkSize' : 1024 * 1024,
            'testChunks': false
        });

        this._allowed_mime_types = [
           'image/jpeg',
           'image/png',
           'image/gif',
           'audio/mpeg',
           'video/mpeg',
           'video/mp4',
           'video/quicktime',
           'video/x-ms-wmv',
           'video/x-msvideo',
           'video/x-flv',
           'video/webm',
           'application/pdf'
       ];

        this._colors = {
            'success'   : '#38b5e9',
            'error'     : '#FF0000',
            'upload'    : '#24bc7a',
            'processing': '#FF4500'
        }
    }

    /**
     * Render view
     */
    render() {
        new FoldersTree().fetch({
            siteId: Application.getContext().siteId,
            success: (foldersTree) => {
                let template = this._renderTemplate('Media/uploadView', {
                    foldersTree : foldersTree
                });
                this.$el.html(template);
                this.initFileUpload();
            }
        });

        return this;
    }

    /**
     * Get url to upload
     *
     * @param {Object}  flowFile
     * @param {Object}  flowChunk
     * @param {Boolean} isTest
     */
    _getFlowTarget(flowFile, flowChunk, isTest) {
         return Routing.generate('open_orchestra_api_media_upload', {'folderId' : $('#folderId', this.$el).val()});
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

        let viewContext = this

        this._flow.on('fileAdded', function(file) {
            $('.progress, .flow-list').show();
            let template = viewContext._renderTemplate('Media/uploadProgress', {
                name: file.name,
                size: viewContext._readablizeBytes(file.size),
                id  : file.uniqueIdentifier
            });
            $('.flow-list', viewContext.$el).append(template);
        });

        this._flow.on('filesSubmitted', function(file) {
            viewContext._flow.upload();
        });

        this._flow.on('fileSuccess', function(file, message) {
            _setInfo(
                file.uniqueIdentifier,
                Translator.trans('open_orchestra_media_admin.upload.completed'),
                viewContext._colors.success
            );
        });

        this._flow.on('fileError', function(file, message, chunk) {
            if (500 == chunk.xhr.status) {
                message = Translator.trans('open_orchestra_media_admin.upload.server_error');
            }
            _hideToolbar(file.uniqueIdentifier);
            _setInfo(
                file.uniqueIdentifier,
                Translator.trans('open_orchestra_media_admin.upload.failed') + message,
                viewContext._colors.error
            );
        });

        this._flow.on('fileProgress', function(file) {
            let progress = '';
            let colorCode = viewContext._colors.upload;
            if (file.progress() < 1) {
                progress = Math.floor(file.progress() * 100) + '% '
                    + viewContext._readablizeBytes(file.averageSpeed) + '/s '
                    + viewContext._secondsToStr(file.timeRemaining()) + ' '
                    + Translator.trans('open_orchestra_media_admin.upload.remaining');
            } else {
                progress = Translator.trans('open_orchestra_media_admin.upload.processing');
                colorCode = viewContext._colors.processing;
                _hideToolbar(file.uniqueIdentifier);
            }

            _setInfo(file.uniqueIdentifier, progress, colorCode);

            let progressTotal = Math.floor(viewContext._flow.progress(true) * 100) + '%';
            $('.progress-bar', viewContext.$el).text(progressTotal);
            $('.progress-bar', viewContext.$el).css({width: progressTotal});
        });

        let _setInfo = function(id, text, colorCode) {
            $('#' + id + '> .flow-file-progress', viewContext.$el).text(text);
            $("#" + id, viewContext.$el).css({color: colorCode});
        }

        let _hideToolbar = function(id) {
            let selector = '#' + id + '> .flow-file-pause, #' + id + '> .flow-file-resume, #' + id + '>  .flow-file-cancel';
            $(selector , viewContext.$el).remove();
        }
    }

    /**
     * @param {Int} bytes
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
     * @param {Int} time
     *
     * @return {String}
     */
    _secondsToStr(time) {
        let years = Math.floor(time / 31536000)

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
     * @param {Int} number
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
