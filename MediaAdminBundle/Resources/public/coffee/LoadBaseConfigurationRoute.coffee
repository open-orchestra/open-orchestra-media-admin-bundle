((router) ->
  router.addRoutePattern 'apiMediaEdit', $('#contextual-informations').data('media-edit-url')

  router.route 'folder/:folderId/list/media/:mediaId/edit', 'mediaEdit', (folderId, mediaId) ->
    @initDisplayRouteChanges '#' + folderId
    SuperboxLoad folderId, mediaId
    return

  router.route 'folder/:folderId/list', 'listFolder', (folderId) ->
    @initDisplayRouteChanges()
    GalleryLoad folderId
    return

  router.route 'folder/:folderId/add/media', 'addMedia', (folderId) ->
    @initDisplayRouteChanges '#' + folderId
    MediaAddLoad folderId
    return

) window.appRouter
