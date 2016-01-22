extendView = extendView || {}
extendView['orchestraWysiwygType'] =

  WysiwygTypeModal: (parameters) ->
    modalParameters = 
      domContainer: $('#' + parameters.target, @$el)
      input: parameters.input
      url: parameters.url
      method: if @options.method then @options.method else 'GET'
    @openMediaModal($.extend(modalParameters, 
      galleryView : ['galleryWysiwygView']
    ))
