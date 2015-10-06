WysiwygSelectView = OrchestraView.extend(
  events:
    'click #sendToTiny': 'sendToTiny'
    'change #media_crop_format' : 'changeCropFormat'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'domContainer'
      'html'
      'thumbnails'
      'original'
    ])
    @loadTemplates [
        'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/previewImageView',
        'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media'
      ]
    return

  render: (options) ->
    @setElement $(@options.html).append(@renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/previewImageView'
      src: @options.original
    ))
    @options.domContainer.html @$el

  changeCropFormat: (event) ->
    format = $(event.currentTarget).val()
    image = @options.thumbnails[format] || @options.original
    $('#preview_thumbnail', @$el).attr 'src', image

  sendToTiny: (event) ->
    event.preventDefault()
    modalContainer = @$el.closest(".mediaModalContainer")
    editorId = modalContainer.data("input")
    currentView = @
    tinymce.get(editorId).execCommand(
      'mceInsertContent',
      false,
      do ->
        currentView.renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media',
          media_id: $('#media_crop_id', @el).val()
          media_src: $('#preview_thumbnail', @$el).attr('src')
          media_format: $('#media_crop_format', @$el).val()
        );
    )
    modalContainer.find('.mediaModalClose').click()
)
