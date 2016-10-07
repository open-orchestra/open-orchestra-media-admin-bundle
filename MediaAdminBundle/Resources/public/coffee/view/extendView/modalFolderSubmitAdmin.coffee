extendView = extendView || {}
extendView['modalFolderSubmitAdmin'] = $.extend({}, extendView['submitAdmin'], http_created: (response) ->
  formChannel.trigger('element-created', response[0], response[2].getResponseHeader('document-id'))
)
