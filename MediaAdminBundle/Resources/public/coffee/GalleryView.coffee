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
    if !@options.modal
      @events['click .superbox-img'] = 'superboxOpen'
      if @options.media.get('is_deletable')
        @mediaClass = "media-remove"
        @mediaLogo = "fa-trash-o"
    else
      delete @events['click .superbox-img'] if @events['click .superbox-img']?
      @mediaClass = "media-select"
      @mediaLogo = "fa-check-circle"
    @options.thumbnails = @options.media.get("thumbnails")
    @options.original = @options.media.get("displayed_image")
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
    )
    @options.domContainer.append @$el

  toggleCaption: (event) ->
    @$el.find(".caption").slideToggle(150)

  superboxOpen: ->
    listUrl = Backbone.history.fragment
    Backbone.history.navigate(listUrl + '/media/' + @options.media.id + '/edit')
    showTabMedia(@options.media, listUrl)

  confirmRemoveMedia: (event) ->
    smartConfirm(
      'fa-trash-o',
      $(".delete-confirm-question").text(),
      $(".delete-confirm-explanation").text(),
      callBackParams:
        mediaView: @
      yesCallback: (params) ->
        params.mediaView.removeMedia(event)
    )

  removeMedia : (event) ->
    target = $(event.target)
    $.ajax
      url: @options.media.get("links")._self_delete
      method: 'Delete'
      success: (response) ->
        target.parents(".superbox-list").remove()

  mediaSelect : (event) ->
    event.preventDefault()
    modalContainer = @$el.closest(".mediaModalContainer")
    intputName = modalContainer.data('input')
    $('#' + intputName).val @options.media.id
    $('#previewImage_' + intputName).attr 'src', @$el.find('.superbox-img img').attr('src')
    modalContainer.find('.mediaModalClose').click()
)
