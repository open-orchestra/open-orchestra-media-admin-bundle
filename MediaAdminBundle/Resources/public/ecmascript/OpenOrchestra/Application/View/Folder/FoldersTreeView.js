import AbstractTreeView from '../Tree/AbstractTreeView'
import Folder           from '../../Model/Folder/Folder'
import ApplicationError from '../../../Service/Error/ApplicationError'
import Application      from '../../Application'

/**
 * @class FoldersTreeView
 */
class FoldersTreeView extends AbstractTreeView
{
    /**
     * Initialize
     * @param {FoldersTree} foldersTree
     */
    initialize({foldersTree}) {
        this._foldersTree = foldersTree;
    }

    /**
     * Get the tree template
     * @return {Object}
     * @private
     */
    _getTreeTemplate() {
        return this._renderTemplate('Folder/foldersTreeView',
            {
                foldersTree : this._foldersTree.models
            }
        );
    }

    /**
     * @param {Object} event
     * @param {Object} ui
     * @private
     */
    _startDrag(event, ui) {
        let initialParentId = ui.item.parent('ul').parent('li').data('folder-id');
        if (typeof initialParentId === 'undefined') {
            throw new ApplicationError('undefined parent folder id');
        }
        $(ui.item).data('old-parent-id', initialParentId);
    };

    /**
     * @param {Object} event
     * @param {Object} ui
     * @private
     */
    _sortAction(event, ui) {
        let folderId = $(ui.item).data('folder-id');
        let newParentId = $(ui.item).parent('ul').parent('li').data('folder-id');
        if (typeof newParentId === 'undefined') {
            throw new ApplicationError('undefined new parent folder id');
        }

        if (newParentId != $(ui.item).data('old-parent-id')) {
            let folder = new Folder({id: folderId, parent_id: newParentId});
            folder.save();
        }
    };
}

export default FoldersTreeView;
