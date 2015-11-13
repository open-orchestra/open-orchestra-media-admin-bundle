MediaAddLoad = (folderId) ->
  link = $('#' + folderId)
  title = link.text()
  listUrl = appRouter.generateUrl('listFolder',
    'folderId':  folderId
  )
  $.ajax
    url: link.data('url')
    method: 'GET'
    success: (response) ->
      medias = new GalleryElement
      medias.set response
      MediaAddFormLoad(medias, title, listUrl)

MediaAddFormLoad = (medias, title, listUrl, container = "#content") ->
  uploadUrl = medias.get('links')._self_upload
  $.ajax
    url: medias.get('links')._self_add
    method: 'GET'
    success: (response) ->
      viewClass = appConfigurationView.getConfiguration('media', 'uploadMedia')
      new viewClass(
        html: response
        listUrl: listUrl
        uploadUrl: uploadUrl
        title: title
        domContainer: $(container)
      )

MediaAddUploadLoad = (medias, title, container = "#content") ->
  viewClass = appConfigurationView.getConfiguration('media', 'uploadMedia')
  new viewClass(
    uploadUrl: medias.get('links')._self_upload
    title: title
    domContainer: $(container)
  )
