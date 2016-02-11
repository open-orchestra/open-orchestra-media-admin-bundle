###*
 * @namespace OpenOrchestra:GroupTree
###
window.OpenOrchestra or= {}
window.OpenOrchestra.GroupTree or= {}

###*
 * @class MediaFolderTreeView
###
class OpenOrchestra.GroupTree.MediaFolderTreeView extends OrchestraView

  events:
    'click em.fa': 'toggleItemDisplay'

  ###*
   * @param {Object} options
  ###
  initialize: (options) ->
    @initializer options
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/mediaFolderTree',
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/backToList'
    ]
    return

  ###*
   * @param {Object} options
  ###
  initializer: (options) ->
    @options = options
    @options.listUrl = appRouter.generateUrl('listEntities', entityType: options.entityType) if options.listUrl == undefined
    @options.formView = 'editEntityTab'
    @options.domContainer = @$el

  ###*
   * render
  ###
  render: ->
    @options.domContainer.html @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/groupTree/mediaFolderTree')
    currentView = @
    $.ajax
      url: currentView.options.response.links._self_folder_tree
      method: "GET"
      success: (response) ->
        currentView.options.folders = response
        $.ajax
          url: currentView.options.response.links._role_list_media_folder
          method: "GET"
          success: (response) ->
            currentView.options.roles = response
            currentView.renderHead()
            if not _.isEmpty(currentView.options.folders.children)
              currentView.renderTreeElement()

    @options.domContainer.append @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/backToList',
      listUrl : @options.listUrl
    )

  ###*
   * Render tree element
  ###
  renderTreeElement: ->
    subContainer = @options.domContainer.find('ul').first()
    mediaFolderTreeElementViewClass = appConfigurationView.getConfiguration('group_tab_media_folder_tree_element', 'editEntityTab')
    for rootFolders in @options.folders.children
      new mediaFolderTreeElementViewClass(
        group: @options.response
        folders: rootFolders
        domContainer: subContainer
        roles: @options.roles
      )
    $('.fa', @$el).addClass 'fa-minus-square-o'

  ###*
   * render roles header
  ###
  renderHead: ->
    for role in @options.roles.roles
      @options.domContainer.find('.head-element').first().append '<div class="col-sm-2">' + role.description + '</div>'

  ###*
   * @param {Object} e
  ###
  toggleItemDisplay: (e) ->
    OpenOrchestra.toggleTreeNodeDisplay(e, '.child-folder')

jQuery ->
  appConfigurationView.setConfiguration 'group_tab_media_folder_tree', 'editEntityTab', OpenOrchestra.GroupTree.MediaFolderTreeView
