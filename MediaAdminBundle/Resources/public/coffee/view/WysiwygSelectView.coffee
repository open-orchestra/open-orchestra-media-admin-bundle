WysiwygSelectView = OrchestraView.extend(

  extendView: ['breadcrumbAware']

  events:
    'click #sendToTiny': 'sendToTiny'
    'change #oo_media_crop_format' : 'changeCropFormat'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'domContainer'
      'html'
      'alternatives'
      'original',
      'mediaName'
    ])
    @loadTemplates [
        'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/previewImageView',
        'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media',
        'OpenOrchestraBackofficeBundle:BackOffice:Underscore/Jarvis/header',
        'OpenOrchestraBackofficeBundle:BackOffice:Underscore/Jarvis/footer'
      ]
    return

  render: (options) ->
    @setElement $(@options.html).append(@renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/previewImageView'
      src: @options.original
    ))
    header = @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/Jarvis/header')
    footer = @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/Jarvis/footer')
    @$el.html header + @$el.html() + footer
    @options.domContainer.html @$el
    path = @getPath()
    path.push @options.mediaName
    $('.js-widget-title', @options.domContainer).html path.join(' > ')

  changeCropFormat: (event) ->
    format = $(event.currentTarget).val()
    image = @options.alternatives[format] || @options.original
    $('#preview_thumbnail', @$el).hide()
    $('#preview_thumbnail', @$el).attr 'src', image
    $('#preview_thumbnail', @$el).load ->
      $('#preview_thumbnail', @$el).show()
      return

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
          media_id: $('#oo_media_crop_id', @el).val()
          media_src: $('#preview_thumbnail', @$el).attr('src')
          media_format: $('#oo_media_crop_format', @$el).val()
        );
    )
    modalContainer.find('.mediaModalClose').click()
)
