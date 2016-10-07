extendView = extendView || {}
extendView['folderSubmitAdmin'] = $.extend({}, extendView['submitAdmin'], httpCreated: (response) ->
  listUrl = undefined
  @options.domContainer.modal 'hide'
  launchNotification 'success', $(response[0]).text()
  widgetChannel.trigger 'element-created', this
  listUrl = appRouter.generateUrl('listFolder', 'folderId': response[2].getResponseHeader('document-id'))
  refreshMenu listUrl
  $(document).scrollTop 0
)
