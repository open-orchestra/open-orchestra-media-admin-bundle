import DataTableCollection from '../../../Service/DataTable/Collection/DataTableCollection'
import Media               from '../../Model/Media/Media'

/**
 * @class Medias
 */
class Medias extends DataTableCollection
{
    /**
     * Pre initialize
     */
    preinitialize() {
        this.model = Media;
    }

    /**
     * @param response
     *
     * @returns {Object}
     */
    parse(response) {
        if (response.hasOwnProperty('rights')) {
            this.rights = response.rights;
        }

        return super.parse(response);
    }

    /**
     * @inheritdoc
     */
    toJSON(options) {
        return {
            'medias': super.toJSON(options)
        }
    }

    /**
     * @inheritdoc
     */
    _getSyncUrl(method, options) {
        switch (method) {
            case "read":
                return Routing.generate('open_orchestra_api_media_list');
            case "delete":
                return Routing.generate('open_orchestra_api_media_delete_multiple');
        }
    }
}

export default Medias
