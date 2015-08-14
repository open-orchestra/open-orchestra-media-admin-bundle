SuperboxLoad = (folderId, mediaId) ->
  $.ajax
    url: appRouter.generateUrl('apiMediaEdit', mediaId: mediaId)
    method: 'GET'
    success: (response) ->
      mediaModel = new GalleryModel
      mediaModel.set response
      options =
          domContainer : $('#content')
          media: mediaModel
          listUrl: appRouter.generateUrl('listFolder', folderId: folderId)
      new CropFormView(options)
      ###      viewClass = appConfigurationView.getConfiguration('media', 'showSuperbox')
      new viewClass(
        media: mediaModel
        listUrl: appRouter.generateUrl('listFolder', folderId: folderId))###
  return
