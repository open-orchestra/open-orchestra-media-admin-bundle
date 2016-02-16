###*
 * @namespace OpenOrchestra:GroupTree
###
window.OpenOrchestra or= {}
window.OpenOrchestra.GroupTree or= {}

###*
 * @class MediaFolderTreeElementView
###
class OpenOrchestra.GroupTree.MediaFolderTreeElementView extends OrchestraView

  ###*
   * @param {Object} options
  ###
  initialize: (options) ->
    ###*
     * {Object} domContainer
     * {Object} folders
     * {Object} group
     * {Object} roles
    ###
    @options = @reduceOption(options, [
      'domContainer'
      'folders'
      'group'
      'roles'
    ])
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/mediaFolderTreeElement',
    ]
    return

  ###*
   * render
  ###
  render: ->
    @options.domContainer.append @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/mediaFolderTreeElement',
      folders: @options.folders
    )
    @formInput = @options.domContainer.find('div.form-input').last()
    folderId = @options.folders.folder.folder_id
    folderGroupRoles = @options.group.document_roles.filter (element) ->
      element.document == folderId
      element.type == 'folder'
    formCollectionViewClass = appConfigurationView.getConfiguration('group_tab_media_folder_tree_form', 'editEntityTab')
    new formCollectionViewClass(
      roles: @options.roles.roles
      domContainer: @formInput
      folderGroupRoles: folderGroupRoles
      group: @options.group
      folderElement: @options.folders.folder
    )
    @subFolder = @options.domContainer.find('ul.child-document').last()
    if @options.folders.children.length > 0
      for child of @options.folders.children
        @addChildToView @options.folders.children[child]

  ###*
   * @param {Object} child
  ###
  addChildToView: (child) ->
    mediaFolderTreeElementViewClass = appConfigurationView.getConfiguration('group_tab_media_folder_tree_element', 'editEntityTab')
    new mediaFolderTreeElementViewClass(
      group: @options.group
      folders: child
      domContainer: @subFolder
      roles: @options.roles
    )

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_media_folder_tree_element', 'editEntityTab', OpenOrchestra.GroupTree.MediaFolderTreeElementView
