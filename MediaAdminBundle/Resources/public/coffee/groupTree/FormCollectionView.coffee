FormCollectionView = OrchestraView.extend(
  events:
    'change .value-holder': 'changeInput'
  initialize: (options) ->
    @options = options
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/groupTreeForm',
    ]

  render: ->
    for role in @options.roles
      @options.domContainer.append @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/groupTreeForm',
        role: role
        folder: @options.folderElement
      )
    @setElement @options.domContainer
    if @options.folderGroupRoles != undefined
      for folderGroupRole in @options.folderGroupRoles
        $('select[data-role-name="' + folderGroupRole.name + '"] option[value="' + folderGroupRole.access_type + '"]', @options.domContainer).attr('selected','selected')

  changeInput: (e) ->
    value = $(e.currentTarget).val()
    name = $(e.currentTarget).data('role-name')
    folderId = @options.folderElement.folder_id
    folderGroupRoleData = []
    folderGroupRoleData.push({'folder': folderId, 'access_type': value, 'name': name})
    $.ajax
      url: @options.group.links._self_edit
      method: 'POST'
      data: JSON.stringify(
        media_folder_roles: folderGroupRoleData
      )
)

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_media_folder_tree_form', 'editEntityTab', FormCollectionView
