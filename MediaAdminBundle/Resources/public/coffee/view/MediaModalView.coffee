formChannel = new (Backbone.Wreqr.EventAggregator)
currentModal = null

MediaModalView = OrchestraView.extend(

  extendView: ['breadcrumbAware']

  events:
    'click .mediaModalClose': 'closeModal'
    'click .media-modal-menu-folder>span': 'showFolder'
    'click .media-modal-menu-new-folder': 'openFormFolder'
    'click #backModal': 'backToFolder'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'body'
      'input'
      'domContainer'
      'url'
      'galleryView'
    ])
    @loadTemplates [
      "OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaModalView"
    ]
    return

  render: (options) ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaModalView', @options)

    @initMenu()

    if currentModal != null
      $('.modal-dialog', currentModal).replaceWith $('.modal-dialog', @$el)
    else
      @options.domContainer.html @$el
      currentModal = @$el.detach().appendTo('body')
      currentModal.modal "show"
      currentModal.on 'hidden.bs.modal', ->
        currentModal = null
        return

  initMenu: (activeNode) ->
    @updateNavigation(activeNode) if activeNode?

    opts =
      accordion: true
      speed: $.menu_speed
      closedSign: "<em class=\"fa fa-plus-square-o\"></em>"
      openedSign: "<em class=\"fa fa-minus-square-o\"></em>"
    $(@el).jarvismenu opts

  closeModal: ->
    if currentModal
      currentModal.modal "hide"

  showFolder: (event) ->
    @updateNavigation($(event.target))
    @openGallery($(event.target).parent().attr('id'))

  backToFolder: ->
    @openGallery($('.modal-body-menu nav .active', @el).attr('id'))

  openGallery: (folderId) ->
    displayLoader $(".modal-body-content", @$el)
    GalleryLoad folderId, @options.galleryView, $(".modal-body-content", @$el)

  updateNavigation: (node) ->
    $('.modal-body-menu nav .active', @el).removeClass("active");
    node.parent().addClass("active");

  reloadFolder: ->
    displayLoader $('.modal-body-menu', @$el)
    refreshMenu()
    $.ajax
      url: @options.url
      method: 'GET'
      context: this
      success: (response) ->
        $('.modal-body-menu', currentModal).html response
        @initMenu($('#media-modal-' + $('#oo_folder_id').val()))
    return

  openFormFolder: (event) ->
    event.preventDefault()
    @updateNavigation($(event.target))
    @listenToOnce(formChannel, 'formSubmit', @reloadFolder)
    displayLoader $(".modal-body-content", @$el)
    domContainer = $(".modal-body-content", @$el)
    title = @getPath().join(' > ')
    $.ajax
      url: $(event.currentTarget).data('url')
      method: 'GET'
      success: (response) ->
        viewClass = appConfigurationView.getConfiguration('media', 'showMediaForm')
        new viewClass(
            html: response
            domContainer: domContainer
            title: title
          )
)
