formChannel = new (Backbone.Wreqr.EventAggregator)

MediaModalView = OrchestraView.extend(

  extendView: ['breadcrumbAware']

  events:
    'hidden.bs.modal': 'removeModal'
    'click .mediaModalClose': 'closeModal'
    'click .media-modal-menu-folder>span': 'showFolder'
    'click .media-modal-menu-new-folder': 'openFormFolder'
    'click #backModal': 'backToFolder'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'url'
      'galleryView'
      'mediaType'
      'input'
      'body'
    ])
    @loadTemplates [
      "OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaModalView"
    ]
    return

  render: (options) ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaModalView', @options)
    @initMenu()
    @$el.appendTo('body')
    @$el.modal "show"

  initMenu: (activeNode) ->
    @updateNavigation(activeNode) if activeNode?

    @opts =
      accordion: true
      speed: $.menu_speed
      closedSign: "<em class=\"fa fa-plus-square-o\"></em>"
      openedSign: "<em class=\"fa fa-minus-square-o\"></em>"
    $(@el).jarvismenu @opts

  closeModal: ->
    @$el.modal "hide"

  removeModal: ->
    @$el.unbind()
    @$el.remove()

  showFolder: (event, message) ->
    @updateNavigation($(event.target))
    @openGallery $(event.target).parent().attr('id')
    $(".modal-body-flash", @$el).html ''
    if typeof message != 'undefined'
      $(".modal-body-flash", @$el).addClass('flash-bag-active')
      $(".modal-body-content", @$el).addClass('flash-bag-active')
      viewClass = appConfigurationView.getConfiguration('folder', 'showFlashBag')
      new viewClass(
        html: message
        domContainer: $(".modal-body-flash", @$el)
      )
    else
      $(".modal-body-flash", @$el).html ''
      $(".modal-body-flash", @$el).removeClass('flash-bag-active')
      $(".modal-body-content", @$el).removeClass('flash-bag-active')

  backToFolder: ->
    @openGallery($('.modal-body-menu nav .active', @el).attr('id'))

  openGallery: (folderId) ->
    displayLoader $(".modal-body-content", @$el)
    GalleryLoad folderId, @options.galleryView, $(".modal-body-content", @$el), @options.mediaType

  updateNavigation: (node) ->
    $('.modal-body-menu nav .active', @el).removeClass("active");
    node.parent().addClass("active");

  reloadFolder: (message, folderId) ->
    displayLoader $('.modal-body-menu', @$el)

    refreshMenu()
    $.ajax
      url: @options.url
      method: 'GET'
      context: this
      success: (response) ->
        $('.modal-body-menu', @$el).html response
        @initMenu()
        @showFolder { target: $('#media-modal-' + folderId + '>span') }, message
        opts = @opts
        $(".modal-body-menu", @el).find("a.active").each ->
          $(this).parents("ul").slideDown opts.speed
          $(this).parents("ul").parent("li").find("b:first").html opts.openedSign
          $(this).parents("ul").parent("li").addClass "open"
          return
    return

  openFormFolder: (event) ->
    event.preventDefault()
    @updateNavigation($(event.target))
    @listenToOnce(formChannel, 'element-created', @reloadFolder)
    displayLoader $(".modal-body-content", @$el)
    domContainer = $(".modal-body-content", @$el)
    title = @getPath().join(' > ')
    $.ajax
      url: $(event.currentTarget).data('url')
      method: 'GET'
      success: (response) ->
        viewClass = appConfigurationView.getConfiguration('modal_folder_form', 'folder')
        new viewClass(
            html: response
            domContainer: domContainer
            title: title
          )
)
