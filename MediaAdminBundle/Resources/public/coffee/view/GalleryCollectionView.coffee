GalleryCollectionView = OrchestraView.extend(

  extendView: ['breadcrumbAware']

  events:
    'click a.ajax-add': 'clickAdd'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'folderId'
      'medias'
      'title'
      'listUrl'
      'domContainer'
      'modal'
      'galleryView'
    ])
    @loadTemplates [
      "OpenOrchestraMediaAdminBundle:BackOffice:Underscore/galleryCollectionView",
      "OpenOrchestraMediaAdminBundle:BackOffice:Underscore/galleryView",
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media',
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/audio'
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/galleryCollectionView'
      links: @options.medias.get('links')
      BOroute: '#' + appRouter.generateUrl('addMedia',
        folderId: @options.folderId
      )
    )
    @options.domContainer.html @$el
    if !@options.modal
      $('.js-widget-title', @options.domContainer).text @options.title
      @addConfigurationButton()
      @addDeleteButton()
    else
      $('.js-widget-title', @options.domContainer).text @getPath().join(' > ')
    for mediaKey of @options.medias.get(@options.medias.get('collection_name'))
      @addElementToView(@options.medias.get(@options.medias.get('collection_name'))[mediaKey])

  addElementToView: (mediaData) ->
    mediaModel = new GalleryModel
    mediaModel.set mediaData
    viewClass = appConfigurationView.getConfiguration('media', 'showGallery')
    new viewClass(@addOption(
      media: mediaModel
      domContainer: this.$el.find('.superbox')
      extendView: @options.galleryView
    ))
    return

  clickAdd: (event) ->
    if @options.domContainer.parents('.mediaModalContainer').length > 0
      event.preventDefault()
      viewClass = appConfigurationView.getConfiguration('media', 'uploadMedia')
      new viewClass(
        uploadUrl: @options.medias.get('links')._self_add
        title: @options.title
        domContainer: @options.domContainer
      )

  addConfigurationButton: ->
    if @options.medias.get('links')._self_folder != undefined
      viewClass = appConfigurationView.getConfiguration('media', 'addFolderConfigurationButton')
      new viewClass(@options)

  addDeleteButton: ->
    if @options.medias.get('is_folder_deletable')
      if @options.medias.get('links')._self_delete != undefined
        viewClass = appConfigurationView.getConfiguration('media', 'addFolderDeleteButton')
        new viewClass(@options)
)
