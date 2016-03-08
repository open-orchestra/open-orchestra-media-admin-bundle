###*
 * @class FormMediaFolderCollectionView
###
class OpenOrchestra.GroupTree.FormMediaFolderCollectionView extends OpenOrchestra.GroupTree.AbstractFormCollectionView
  getElement: ->
    return 'folderElement'
  getId: ->
    return 'folder_id'
  getType: ->
    return 'folder'
  getGroupRoles: ->
    return 'folderGroupRoles'

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_media_folder_tree_form', 'editEntityTab', OpenOrchestra.GroupTree.FormMediaFolderCollectionView
