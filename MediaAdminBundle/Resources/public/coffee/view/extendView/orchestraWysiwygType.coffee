extendView = extendView || {}
extendView['orchestraWysiwygType'] =

  WysiwygTypeModal: (parameters) ->
    modalParameters = 
      domContainer: $(parameters.selector, @$el)
      input: parameters.input
      url: $(parameters.selector, @$el).data('url')
      method: if @options.method then @options.method else 'GET'
    @openMediaModal($.extend(modalParameters, 
      galleryView : ['galleryWysiwygView']
    ))
