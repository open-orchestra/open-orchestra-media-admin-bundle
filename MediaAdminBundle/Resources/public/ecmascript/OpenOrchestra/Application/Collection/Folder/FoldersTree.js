import OrchestraCollection from '../OrchestraCollection'
import FolderTree          from '../../Model/Folder/FolderTree'

/**
 * @class FoldersTree
 */
class FoldersTree extends OrchestraCollection
{
    /**
     * Pre initialize
     */
    preinitialize() {
        this.model = FolderTree;
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

export default FoldersTree
