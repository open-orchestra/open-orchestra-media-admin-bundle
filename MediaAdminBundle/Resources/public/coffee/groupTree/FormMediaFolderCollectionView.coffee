###*
 * @namespace OpenOrchestra:GroupTree
###
window.OpenOrchestra or= {}
window.OpenOrchestra.GroupTree or= {}

###*
 * @class FormMediaFolderCollectionView
###
class OpenOrchestra.GroupTree.FormMediaFolderCollectionView extends OrchestraView

  events:
    'change .value-holder': 'changeInput'

  ###*
   * @param {Object} options
  ###
  initialize: (options) ->
    ###*
     * {Object} domContainer
     * {Object} folderElement
     * {Array}  folderGroupRoles
     * {Object} group
     * {Array}  roles
    ###
    @options = @reduceOption(options, [
      'domContainer'
      'folderElement'
      'folderGroupRoles'
      'group'
      'roles'
    ])
    @loadTemplates [
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/groupTree/groupTreeForm',
    ]

  ###*
   * render
  ###
  render: ->
    for role in @options.roles
      @options.domContainer.append @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/groupTree/groupTreeForm',
        role: role
        document: @options.folderElement
      )
    @setElement @options.domContainer
    if @options.folderGroupRoles != undefined
      for folderGroupRole in @options.folderGroupRoles
        $('select[data-role-name="' + folderGroupRole.name + '"] option[value="' + folderGroupRole.access_type + '"]', @options.domContainer).attr('selected','selected')

  ###*
   * @param {Object} e
  ###
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

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_media_folder_tree_form', 'editEntityTab', OpenOrchestra.GroupTree.FormMediaFolderCollectionView
