import AbstractBehavior from './AbstractBehavior'
import MediaModalView   from '../../MediaModal/View/MediaModalView'
import Application      from '../../../Application/Application'

/**
 * @class MediaChoice
 */
class MediaChoice extends AbstractBehavior
{
    /**
     * get extra events
     *
     * @return {Object}
     */
    getExtraEvents() {
        return {
            'click .btn-browse': '_browseMedia',
            'click .btn-remove': '_removeMedia'
        }
    }

    /**
     * Open the media modal
     *
     * @param {Object} event
     */
    _browseMedia(event)
    {
        event.preventDefault();
        let formId = $(event.currentTarget).data('form-id');
        let mediaModalView = new MediaModalView({
            selectCallback: function(media) {
                $('#preview-' + formId + ' .no-media').hide();
                $('#preview-' + formId + ' img').attr('src', media.src);
                $('#' + formId + '_id').val(media.id);
                $('#' + formId + '_format').val(media.format);
                $('#' + formId + '_alt').val(media.alt);
                $('#' + formId + '_legend').val(media.legend);
            },
            filterType   : $(event.currentTarget).data('media-type')
        });

        Application.getRegion('modal').html(mediaModalView.render().$el);
        mediaModalView.show();

        return false;
    }

    /**
     * Remove the selected media
     *
     * @param {Object} event
     */
    _removeMedia(event)
    {
        event.preventDefault();
        let formId = $(event.currentTarget).data('form-id');
        $('#preview-' + formId + ' img').attr('src', '/img/no-media.png');
        $('#' + formId + '_id').val('');
        $('#' + formId + '_format').val('');
        $('#' + formId + '_alt').val('');
        $('#' + formId + '_legend').val('');
        $('#preview-' + formId + ' .no-media').show();
    }

    /**
     * return selector
     *
     * @return {String}
     */
    getSelector() {
        return '.orchestra-media-choice';
    }
}

export default (new MediaChoice);
