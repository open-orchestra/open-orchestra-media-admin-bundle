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
          alternatives: viewContext.options.alternatives
          original: viewContext.options.original
          mediaName: viewContext.options.media.get('name')
        )

  sendToTiny: ->
    tagTemplate = 'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media'
    if 0 == @options.media.get('mime_type').indexOf('audio/')
      tagTemplate = 'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/audio'

    tag = @renderTemplate tagTemplate,
      media_id: @options.media.get('id')
      media_src: @options.original
      media_format: null

    modalContainer = @$el.closest(".mediaModalContainer")
    editorId = modalContainer.data("input")
    tinymce.get(editorId).execCommand(
      'mceInsertContent',
      false,
      tag
    )
    modalContainer.find('.mediaModalClose').click()
