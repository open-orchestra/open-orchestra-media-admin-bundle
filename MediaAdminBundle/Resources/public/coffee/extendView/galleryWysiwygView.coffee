extendView = extendView || {}
extendView['galleryWysiwygView'] =
  mediaSelect: (event) ->
    event.preventDefault()
    if 0 == @options.media.get('mime_type').indexOf('image/')
      @chooseFormat()
    else
      @sendToTiny()

  chooseFormat: ->
    viewContext = @
    $.ajax
      url: @options.media.get('links')._self_crop
      method: "GET"
      success: (response) ->
        viewClass = appConfigurationView.getConfiguration('media', 'showWysiwygSelect')
        new viewClass(
          domContainer: viewContext.$el.closest(".modal-body-content")
          html: response
          thumbnails: viewContext.options.thumbnails
          original: viewContext.options.original
        )

  sendToTiny: ->
    modalContainer = @$el.closest(".mediaModalContainer")
    editorId = modalContainer.data("input")
    src = @options.original;
    id = @options.media.get('id');
    tinymce.get(editorId).execCommand(
      'mceInsertContent',
      false,
      '<img class="tinymce-media" src="' + src + '" data-id="' + id + '" />'
    )
    modalContainer.find('.mediaModalClose').click()
