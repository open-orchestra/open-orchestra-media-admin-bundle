import DataTableCollection from '../../../Service/DataTable/Collection/DataTableCollection'
import Folder              from '../../Model/Folder/Folder'

/**
 * @class Folders
 */
class Folders extends DataTableCollection
{
    /**
     * Pre initialize
     */
    preinitialize() {
        this.model = Folder;
    }

    /**
     * @inheritdoc
     */
    toJSON(options) {
        return {
            'folders': super.toJSON(options)
        }
    }

    /**
     * @inheritdoc
     */
    _getSyncUrl(method, options) {
        switch (method) {
            case "read":
                return Routing.generate('open_orchestra_api_folder_list_tree', {'siteId': options.siteId});
        }
    }
}

export default Folders
