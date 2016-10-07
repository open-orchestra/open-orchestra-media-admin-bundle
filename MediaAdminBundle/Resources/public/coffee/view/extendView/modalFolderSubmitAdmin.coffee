extendView = extendView || {}
extendView['modalFolderSubmitAdmin'] = $.extend({}, extendView['submitAdmin'], httpCreated: (response) ->
  formChannel.trigger('element-created', response[0], response[2].getResponseHeader('document-id'))
)
