((router) ->
  router.addRoutePattern('listFolder', 'folder/:folderId/list');
  router.addRoutePattern('addMedia', 'folder/:folderId/add/media');
  router.addRoutePattern('mediaEdit', 'folder/:folderId/list/media/:mediaId/edit');

  router.route 'folder/:folderId/list/media/:mediaId/edit', 'mediaEdit', (folderId, mediaId) ->
    @initDisplayRouteChanges '#' + folderId
    @addRoutePattern 'apiMediaEdit', $('#' + folderId).data('media-edit-url')
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
