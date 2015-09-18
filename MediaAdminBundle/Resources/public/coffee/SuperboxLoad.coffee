SuperboxLoad = (folderId, mediaId) ->
  $.ajax
    url: appRouter.generateUrl('apiMediaEdit', mediaId: mediaId)
    method: 'GET'
    success: (response) ->
      mediaModel = new GalleryModel
      mediaModel.set response
      listUrl = appRouter.generateUrl('listFolder', folderId: folderId)
      showTabMedia(mediaModel, listUrl)

  return

showTabMedia = (mediaModel, listUrl) ->
  options =
    media: mediaModel
    listUrl: listUrl
    entityType: 'media'

  tabViewClass = appConfigurationView.getConfiguration(options.entityType, 'showTab')
  tabView = new tabViewClass(
    domContainer : $('#content'),
    title : mediaModel.get('title')
  )

  activeModalMeta = true
  if (mediaModel.get('mime_type').indexOf('image') > -1)
    activeModalMeta = false
    elementTabCropFormClass = appConfigurationView.getConfiguration(options.entityType, 'showCropForm')
    tabView.addPanel('Crop', 'crop',new elementTabCropFormClass(options), true)

  elementTabMetaFormClass = appConfigurationView.getConfiguration(options.entityType, 'showMetaForm')
  tabView.addPanel('Meta', 'meta',new elementTabMetaFormClass(options), activeModalMeta)