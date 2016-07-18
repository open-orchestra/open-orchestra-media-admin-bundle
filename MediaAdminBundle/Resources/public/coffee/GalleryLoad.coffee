GalleryLoad = (folderId, galleryView, target, mediaType) ->
  modal = true
  if typeof target is "undefined"
    target = "#content"
    modal = false
  if (modal)
    link = $(target).parent().find('#' + folderId)
  else
    link = $('#' + folderId)
  title = link.text()
  listUrl = Backbone.history.fragment
  $.ajax
    url: link.data('url')
    method: 'GET'
    success: (response) ->
      medias = new GalleryElement
      medias.set response
      viewClass = appConfigurationView.getConfiguration('media', 'showGalleryCollection')
      new viewClass(
        folderId: folderId
        medias: medias
        title: title
        listUrl: listUrl
        domContainer: $(target)
        modal: modal
        galleryView: galleryView
        mediaType: mediaType
      )
