###*
 * @namespace OpenOrchestra:GroupTree
###
window.OpenOrchestra or= {}
window.OpenOrchestra.GroupTree or= {}

###*
 * @class FormMediaFolderCollectionView
###
class OpenOrchestra.GroupTree.FormMediaFolderCollectionView extends OpenOrchestra.GroupTree.AbstractFormCollectionView
  ###*
   * getElement
  ###
  getElement: ->
    return 'folderElement'
  ###*
   * getId
  ###
  getId: ->
    return 'folder_id'
  ###*
   * getType
  ###
  getType: ->
    return 'folder'
  ###*
   * getGroupRoles
  ###
  getGroupRoles: ->
    return 'folderGroupRoles'

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_media_folder_tree_form', 'editEntityTab', OpenOrchestra.GroupTree.FormMediaFolderCollectionView
