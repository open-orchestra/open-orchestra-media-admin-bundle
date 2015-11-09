FolderFormView = OrchestraModalView.extend(

  onViewReady: ->
    if @options.submitted
      listUrl = appRouter.generateUrl('listFolder',
        'folderId': $('#folder_id', @$el).val()
      )
      displayMenu(listUrl)
)

jQuery ->
  appConfigurationView.setConfiguration('folder', 'showOrchestraModal', FolderFormView)
