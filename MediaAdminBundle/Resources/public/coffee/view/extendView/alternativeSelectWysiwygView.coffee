extendView = extendView || {}

extendView['alternativeSelectWysiwygView'] =

  sendMedia: (event) ->
    event.preventDefault()
    modalContainer = @$el.closest(".mediaModalContainer")
    editorId = modalContainer.data("input")
    currentView = this
    tinymce.get(editorId).execCommand(
      'mceInsertContent',
      false,
      do ->
        currentView.renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media',
          media_id: $('#oo_media_crop_id', @el).val()
          media_src: $('#preview_thumbnail', @$el).attr('src')
          media_format: $('#oo_media_crop_format', @$el).val()
        );
    )
    modalContainer.find('.mediaModalClose').click()
