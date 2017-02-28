import Application              from '../../../../Application/Application'
import MediaModalView           from '../../../MediaModal/View/MediaModalView'
import BBcodeTransformerManager from '../OrchestraBBCode/BBcodeTransformerManager'

/**
 * @class MediaPlugin
 */
class MediaPlugin
{
    /**
     * Init plugin
     * @param {Editor} editor
     */
    init(editor) {
        editor.addButton('media', {
            icon         : 'media',
            tooltip      : 'Insert media',
            stateSelector: 'img.tinymce-media',
            onclick      : () => {
                let mediaModalView = new MediaModalView({
                    selectCallback: function(media) {
                        let tag = '<img class="tinymce-media" data-mce-resize="false"'
                            + ' src="' + media.src + '"'
                            + ' alt="' + media.alt + '"'
                            + ' data-id="' + media.id + '"'
                            + ' data-format="' + media.format + '"'
                            + ' data-legend="' + media.legend + '"'
                            + '>';

                        editor.execCommand('mceInsertContent', false, tag);
                    }
                });

                Application.getRegion('modal').html(mediaModalView.render().$el);
                mediaModalView.show();
            }
        });

        let tinyMcePattern = '<img[^>]*class="tinymce-media"[^>]*'
            + 'src="([^"]*)"[^>]*'
            + 'alt="([^"]*)"[^>]*'
            + 'data-id="([^"]*)"[^>]*'
            + 'data-format="([^"]*)"[^>]*'
            + 'data-legend="([^"]*)"[^>]*'
            + '>';
        let bbcodePattern = '[media={"format":"$4","alt":"$2","legend":"$5"}]$3[/media]';

        BBcodeTransformerManager.addHtmlToBbcodeTransformer(tinyMcePattern, bbcodePattern);
    }

    /**
     * Information plugin
     */
    getInfo(){
        return {
            longname: 'Orchestra Media Plugin',
            author: 'open orchestra',
            infourl: 'www.open-orchestra.com'
        };
    }
}

tinymce.PluginManager.add('media', MediaPlugin);
