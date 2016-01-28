FolderFormView = OrchestraModalView.extend(

  onViewReady: ->
    if @options.submitted
      listUrl = appRouter.generateUrl('listFolder',
        'folderId': $('#oo_folder_id', @$el).val()
      )
      refreshMenu(listUrl)
)

jQuery ->
  appConfigurationView.setConfiguration('folder', 'showOrchestraModal', FolderFormView)
