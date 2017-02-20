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
            'click .btn-browse': '_openModal',
            'click .btn-remove': '_removeMedia'
        }
    }

    _openModal(event)
    {
        event.preventDefault();
        let formId = $(event.currentTarget).data('form-id');
        let mediaModalView = new MediaModalView({
            selectCallback: function(media) {
                $('#preview-' + formId + ' span').hide();
                $('#preview-' + formId + ' img').attr('src', media.src);
                $('#' + formId + '_id').val(media.id);
                $('#' + formId + '_format').val(media.format);
                $('#' + formId + '_alt').val(media.alt);
                $('#' + formId + '_legend').val(media.legend);
            }
        });

        Application.getRegion('modal').html(mediaModalView.render().$el);
        mediaModalView.show();

        return false;
    }

    _removeMedia(event)
    {
        event.preventDefault();
        let formId = $(event.currentTarget).data('form-id');
        $('#preview-' + formId + ' img').attr('src', '');
        $('#' + formId + '_id').val('');
        $('#' + formId + '_format').val('');
        $('#' + formId + '_alt').val('');
        $('#' + formId + '_legend').val('');
        $('#preview-' + formId + ' span').show();
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
