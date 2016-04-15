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
    @mediaViews = []
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/galleryCollectionView',
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/galleryView',
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/media',
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/TinyMce/audio'
    ]
    return

  render: ->
    OpenOrchestra.Media.Channel.bind 'mediasFiltered', @filterMedias, @

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
      OpenOrchestra.RibbonButton.ribbonFormButtonView.setFocusedView @, '.ribbon-form-button'
    else
      $('.js-widget-title', @options.domContainer).text @getPath().join(' > ')
      OpenOrchestra.RibbonButton.ribbonFormButtonModalView.setFocusedView @, '.modal-form-button'
    @addFilterWidget()
    @renderCollection()

  renderCollection: ->
    for mediaKey of @options.medias.get(@options.medias.get('collection_name'))
      @addElementToView(@options.medias.get(@options.medias.get('collection_name'))[mediaKey])

  clearElements: ->
    for mediaView in @mediaViews
      mediaView.remove()
    @mediaViews = []

  addElementToView: (mediaData) ->
    mediaModel = new GalleryModel
    mediaModel.set mediaData
    viewClass = appConfigurationView.getConfiguration('media', 'showGallery')
    mediaView = new viewClass(@addOption(
      media: mediaModel
      domContainer: this.$el.find('.superbox')
      extendView: @options.galleryView
    ))
    @mediaViews.push(mediaView)
    return

  clickAdd: (event) ->
    if @options.domContainer.parents('.mediaModalContainer').length > 0
      event.preventDefault()
      viewClass = appConfigurationView.getConfiguration('media', 'uploadMedia')
      new viewClass(
        uploadUrl: @options.medias.get('links')._self_add
        isModal: 'true'
        title: @options.title
        domContainer: @options.domContainer
      )

  addConfigurationButton: ->
    if @options.medias.get('links')._self_folder != undefined
      viewClass = appConfigurationView.getConfiguration('media', 'addFolderConfigurationButton')
      new viewClass($.extend(@options, domContainer: @$el))

  addFilterWidget: ->
    mediaTypeFilterViewClass = appConfigurationView.getConfiguration('media', 'addMediaTypeFilter')
    new mediaTypeFilterViewClass(@addOption(
      viewContainer: @
      widget_index: 2
      media_types_url: @options.medias.get('links')._media_types
    ))

  addDeleteButton: ->
    if @options.medias.get('is_folder_deletable')
      if @options.medias.get('links')._self_delete != undefined
        viewClass = appConfigurationView.getConfiguration('media', 'addFolderDeleteButton')
        new viewClass($.extend(@options, domContainer: @$el))

  filterMedias: (mediaCollection)->
    @options.medias = mediaCollection
    @clearElements()
    @renderCollection()
)
