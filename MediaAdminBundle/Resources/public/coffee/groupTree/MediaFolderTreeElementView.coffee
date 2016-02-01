MediaFolderTreeElementView = OrchestraView.extend(
  initialize: (options) ->
    @options = options
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/mediaFolderTreeElement',
    ]
    return

  render: ->
    @options.domContainer.append @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/mediaFolderTreeElement',
      folders: @options.folders
    )
    @formInput = @options.domContainer.find('div.form-input').last()
    folderId = @options.folders.folder.folder_id
    folderGroupRoles = @options.group.media_folder_roles.filter (element) ->
      element.folder == folderId
    formCollectionViewClass = appConfigurationView.getConfiguration('group_tab_media_folder_tree_form', 'editEntityTab')
    new formCollectionViewClass(
      roles: @options.roles.roles
      domContainer: @formInput
      folderGroupRoles: folderGroupRoles
      group: @options.group
      folderElement: @options.folders.folder
    )
    @subFolder = @options.domContainer.find('ul.child-folder').last()
    if @options.folders.children.length > 0
      for child of @options.folders.children
        @addChildToView @options.folders.children[child]

  addChildToView: (child) ->
    mediaFolderTreeElementViewClass = appConfigurationView.getConfiguration('group_tab_media_folder_tree_element', 'editEntityTab')
    new mediaFolderTreeElementViewClass(
      group: @options.group
      folders: child
      domContainer: @subFolder
      roles: @options.roles
    )
)

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_media_folder_tree_element', 'editEntityTab', MediaFolderTreeElementView
