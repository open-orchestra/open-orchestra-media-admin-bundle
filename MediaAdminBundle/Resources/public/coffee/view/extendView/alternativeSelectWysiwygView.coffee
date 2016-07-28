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
          media_id: $('#oo_select_format_id', @el).val()
          media_src: $('#preview_thumbnail', @$el).attr('src')
          media_format: $('#oo_select_format_format', @$el).val()
          style: null
        )
    )
    modalContainer.find('.mediaModalClose').click()
    @remove()
