extendView = extendView || {}
extendView['orchestraMediaAbstractType'] =

  clearMedia: (event) ->
    event.preventDefault()
    target = $(event.currentTarget)
    inputId = '#' + target.data('input')
    previewId = '#previewImage_' + target.data('input')
    $(inputId + '_id').val ''
    $(inputId + '_format').val ''
    $(previewId).removeAttr('src')

  openMediaModal: (options) ->
    viewClass = appConfigurationView.getConfiguration('media', 'showMediaModal')
    $.ajax
      url: options.url
      method: options.method
      success: (response) ->
        new viewClass($.extend(options,
          body: response
        ))
      error: ->
        new viewClass($.extend(options,
          body: 'Error while loading'
        ))
    return

  launchMediaModal: (event) ->
    event.preventDefault()
    target = $(event.currentTarget)
    {
      domContainer: $('#' + target.data("target"), @$el)
      input: target.data("input")
      url : target.data("url")
      method: if @options.method then @options.method else 'GET'
    }
