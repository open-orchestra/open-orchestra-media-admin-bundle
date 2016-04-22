GalleryView = OrchestraView.extend(
  events:
    'click span.media-remove': 'confirmRemoveMedia'
    'click span.media-select': 'mediaSelect'
    'mouseenter': 'toggleCaption'
    'mouseleave': 'toggleCaption'

  initialize: (options) ->
    @events = @events || {}
    @options = @reduceOption(options, [
      'modal'
      'media'
      'domContainer'
    ])
    @updateRole = @options.media.get("links")._self_crop && @options.media.get("links")._self_meta

    if !@options.modal
      @events['click .superbox-img'] = 'superboxOpen'
      if @options.media.get('is_deletable')
        @mediaClass = "media-remove"
        @mediaLogo = "fa-trash-o"
    else
      delete @events['click .superbox-img'] if @events['click .superbox-img']?
      @mediaClass = "media-select"
      @mediaLogo = "fa-check-circle"
    @options.alternatives = @options.media.get("alternatives")
    @options.original = @options.media.get("original")
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/galleryView',
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media',
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/audio'
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/galleryView',
      media: @options.media
      mediaClass: @mediaClass
      mediaLogo: @mediaLogo
      updateRole: @updateRole
    )
    @options.domContainer.append @$el

  toggleCaption: (event) ->
    @$el.find(".caption").slideToggle(150)

  superboxOpen: ->
    if @updateRole
      listUrl = Backbone.history.fragment
      Backbone.history.navigate(listUrl + '/media/' + @options.media.id + '/edit')
      showTabMedia(@options.media, listUrl)

  confirmRemoveMedia: (event) ->
    smartConfirm(
      'fa-trash-o',
      $(".delete-confirm-question").text(),
      $(".delete-confirm-explanation").text(),
      callBackParams:
        mediaView: this
      yesCallback: (params) ->
        params.mediaView.removeMedia(event)
    )

  removeMedia: (event) ->
    target = $(event.target)
    $.ajax
      url: @options.media.get("links")._self_delete
      method: 'Delete'
      success: (response) ->
        Backbone.history.loadUrl(Backbone.history.getFragment())

  mediaSelect: (event) ->
    event.preventDefault()
    if 0 == @options.media.get('mime_type').indexOf('image/')
      @chooseFormat()
    else
      @sendMedia()

  chooseFormat: ->
    viewContext = this
    extendView = []
    extendView = ['alternativeSelectWysiwygView'] if @wysiwygContext
    $.ajax
      url: @options.media.get('links')._self_select_format
      method: "GET"
      success: (response) ->
        viewClass = appConfigurationView.getConfiguration('media', 'showWysiwygSelect')
        new viewClass(
          domContainer: viewContext.$el.closest(".modal-body-content")
          html: response
          alternatives: viewContext.options.alternatives
          original: viewContext.options.original
          mediaName: viewContext.options.media.get('name')
          extendView: extendView
        )

  sendMedia: ->
    modalContainer = @$el.closest('.mediaModalContainer')
    intputName = modalContainer.data('input')
    $('#' + intputName + '_id').val @options.media.id
    $('#previewImage_' + intputName).attr 'src', @$el.find('.superbox-img img').attr('src')
    modalContainer.find('.mediaModalClose').click()
)
