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
        }
    }
}

export default Medias
