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
    event.preventDefault()
    if $('#main .' + $(event.target).attr('class').split(' ').join(' .')).length
      displayLoader('div[role="container"]')
      Backbone.history.navigate(appRouter.generateUrl('addMedia',
        'folderId':  @options.folderId
      ))
      MediaAddFormLoad(@options.medias, @options.title, @options.listUrl)

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
