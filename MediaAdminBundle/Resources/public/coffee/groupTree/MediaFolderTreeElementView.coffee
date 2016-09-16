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

    # This view not needed groupTreeForm template, there is loaded here to optimize number of request
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/mediaFolderTreeElement',
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/groupTree/groupTreeForm',
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
    folderGroupRoles = @options.group.model_roles.filter (element) ->
      element.model_id == folderId
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
