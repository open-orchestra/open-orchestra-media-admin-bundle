import OrchestraModel from '../OrchestraModel'

/**
 * @class Folder
 */
class Folder extends OrchestraModel
{
    /**
     * @inheritdoc
     */
    _getSyncUrl(method, options) {
        let urlParameter = options.urlParameter || {};
        switch (method) {
            case "update":
                return Routing.generate('open_orchestra_api_folder_move', urlParameter);
            case "delete":
                urlParameter.folderId = this.get('id');
                return Routing.generate('open_orchestra_api_folder_delete', urlParameter);
        }
    }
}

export default Folder
