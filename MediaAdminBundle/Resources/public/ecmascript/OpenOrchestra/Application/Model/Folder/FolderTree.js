import OrchestraModel from 'OpenOrchestra/Application/Model/OrchestraModel'
import Folder         from 'OpenOrchestra/Application/Model/Folder/Folder'

/**
 * @class FolderTree
 */
class FolderTree extends OrchestraModel
{
    /**
     * Parse server response to create nested object
     * @param response
     *
     * @returns {Object}
     */
    parse(response) {
        if (response.hasOwnProperty('folder')) {
            response.folder = new Folder(response.folder, {parse: true});
        }
        if (response.hasOwnProperty('children')) {
            let children = [];
            for (let folderTree of response.children) {
                children.push(new FolderTree(this.parse(folderTree)))
            }
            response.children = children;
        }

        return response;
    }
}

export default FolderTree
