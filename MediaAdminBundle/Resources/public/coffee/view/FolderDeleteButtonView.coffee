FolderDeleteButtonView = OrchestraView.extend(
  events:
    'click i.ajax-folder-delete': 'clickDeleteFolder'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'medias'
      'domContainer'
    ])
    @loadTemplates [
      "OpenOrchestraMediaAdminBundle:BackOffice:Underscore/widgetFolderDeleteButton"
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/widgetFolderDeleteButton')
    addCustomJarvisWidget(@$el, @options.domContainer)
    return

  clickDeleteFolder: (event) ->
    event.preventDefault()
    smartConfirm(
      'fa-trash-o',
      $('.folder-delete').data('title'),
      $('.folder-delete').data('text'),
      callBackParams:
        folderDeleteButtonView: @
      yesCallback: (params) ->
        params.folderDeleteButtonView.deleteFolder()
    )

  deleteFolder: ->
    if @options.medias.get('parent_id') == undefined
      redirectUrl = appRouter.generateUrl('showDashboard')
    else
      redirectUrl = appRouter.generateUrl('listFolder', appRouter.addParametersToRoute(
        'folderId':  @options.medias.get('parent_id')
      ))
    $.ajax
      url:  @options.medias.get('links')._self_delete
      method: 'DELETE'
      success: ->
        appRouter.navigate redirectUrl,
            trigger: true
        refreshMenu redirectUrl
)
