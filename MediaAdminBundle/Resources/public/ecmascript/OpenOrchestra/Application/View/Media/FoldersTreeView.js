import OrchestraView    from '../OrchestraView'
import Nodes            from '../../Collection/Node/Nodes'
import ApplicationError from '../../../Service/Error/ApplicationError'
import Application      from '../../Application'

/**
 * @class FoldersTreeView
 */
class FoldersTreeView extends OrchestraView
{
    /**
     * @inheritdoc
     */
    preinitialize() {
        this.tagName = 'div';
        this.events = {
            'click .tree .toggle-tree' : '_toggleChildrenTree',
            'click .tree .actions .btn-close' : '_openTree',
            'click .tree .actions .btn-open' : '_closeTree'
        }
    }

    /**
     * Initialize
     * @param {FoldersTree} foldersTree
     * @param {string}      language
     */
    initialize({foldersTree, language}) {
        this._foldersTree = foldersTree;
        this._language = language
    }

    /**
     * Render node tree
     */
    render() {
        let template = this._renderTemplate('Media/foldersTreeView',
            {
                foldersTree : this._foldersTree.models,
                language: this._language
            }
        );

        this.$el.html(template);
       this._enableTreeSortable($('.tree .children', this.$el));

        return this;
    }

    /**
     * @param {Object} $tree - Jquery selector
     * @private
     */
    _enableTreeSortable($tree) {
        $tree.sortable({
            connectWith: '.tree .children.sortable-container',
            handle: '.sortable-handler',
            items: '> li.sortable-node',
            zIndex: 20,
            stop: (event, ui) => {
                let $folders = $(ui.item).parent().children();
                let parentId = $(ui.item).parent().parent('li').data('folder-id');
                if (typeof parentId === 'undefined') {
                    throw new ApplicationError('undefined parent node id');
                }
                let folders = [];
                $.each($folders, function(index, folder) {
                    folders.push({'fodler_id': $(folder).data('folder-id')})

                });

                folders = new Folders(fodlers);
                folders.save({
                    urlParameter: {
                        'foldersId': parentId
                    }
                });
            }
        });
    }

    /**
     * @param {Object} event
     * @private
     */
    _toggleChildrenTree(event) {
        $(event.target).toggleClass('closed').parents("div").next('ul').slideToggle();
    }

    /**
     * Open Tree
     *
     * @returns {boolean}
     * @private
     */
    _openTree() {
        $('.tree .toggle-tree', this.$el).removeClass('closed').parents("div").next('ul').slideUp();

        return false;
    }

    /**
     * Close tree
     *
     * @returns {boolean}
     * @private
     */
    _closeTree() {
        $('.tree .toggle-tree', this.$el).addClass('closed').parents("div").next('ul').slideDown();

        return false;
    }

    /**
     * Toggle legend
     *
     * @returns {boolean}
     * @private
     */
    _toggleLegend() {
        $('.legend-panel .panel-body', this.$el).slideToggle();

        return false;
    }
}

export default FoldersTreeView;
