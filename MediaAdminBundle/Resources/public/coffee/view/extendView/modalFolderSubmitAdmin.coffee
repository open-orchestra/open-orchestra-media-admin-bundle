extendView = extendView || {}
extendView['modalFolderSubmitAdmin'] = $.extend({}, extendView['submitAdmin'], http_created: (response, form) ->
  formChannel.trigger('element-created', response, xhr.getResponseHeader('document-id'))
)
