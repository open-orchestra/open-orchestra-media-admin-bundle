class FolderFormView extends OrchestraModalView

  extendView: ['folderSubmitAdmin']

  ###*
   * Refresh menu when form is submitted
  ###
  onViewReady: ->
    if @options.submitted
      refreshMenu()

jQuery ->
  appConfigurationView.setConfiguration('folder', 'showOrchestraModal', FolderFormView)
