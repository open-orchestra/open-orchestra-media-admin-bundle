FolderFormView = OrchestraModalView.extend(
  extendView: ['folderSubmitAdmin']
)

jQuery ->
  appConfigurationView.setConfiguration('folder', 'showOrchestraModal', FolderFormView)
