extendView = extendView || {}
extendView['folderSubmitAdmin'] = $.extend({}, extendView['submitAdmin'], http_created: (response, form) ->
  listUrl = undefined
  @options.domContainer.modal 'hide'
  launchNotification 'success', $(response).text()
  widgetChannel.trigger 'element-created', this
  listUrl = appRouter.generateUrl('listFolder', 'folderId': xhr.getResponseHeader('document-id'))
  refreshMenu listUrl
  $(document).scrollTop 0
)
