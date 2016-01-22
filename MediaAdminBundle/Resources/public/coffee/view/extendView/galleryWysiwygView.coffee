extendView = extendView || {}

extendView['galleryWysiwygView'] =

  wysiwygContext: true

  sendMedia: ->
    tagTemplate = 'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media'
    if 0 == @options.media.get('mime_type').indexOf('audio/')
      tagTemplate = 'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/audio'

    tag = @renderTemplate tagTemplate,
      media_id: @options.media.get('id')
      media_src: @options.media.get('thumbnail')
      media_format: null

    modalContainer = @$el.closest(".mediaModalContainer")
    editorId = modalContainer.data("input")
    tinymce.get(editorId).execCommand(
      'mceInsertContent',
      false,
      tag
    )
    modalContainer.find('.mediaModalClose').click()
