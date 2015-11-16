formChannel = new (Backbone.Wreqr.EventAggregator)
currentModal = null

MediaModalView = OrchestraView.extend(

  extendView : ['breadcrumbAware']

  events:
    'click .mediaModalClose': 'closeModal'
    'click .media-modal-menu-folder' : 'showFolder'
    'click .ajax-add': 'openForm'
    'click .media-modal-menu-new-folder' : 'openFormFolder'

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
        this.remove()
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
    if $(event.target).attr('id')
      @updateNavigation($(event.target))
      displayLoader $(".modal-body-content", @$el)
      GalleryLoad $(event.target).attr('id'), @options.galleryView, $(".modal-body-content", @$el)

  updateNavigation: (node) ->
    $('.modal-body-menu nav .active', @el).removeClass("active");
    node.parent().addClass("active");

  reloadFolder: ->
    displayLoader $('.modal-body-menu', @$el)
    displayMenu()
    $.ajax
      url: @options.url
      method: 'GET'
      context: @
      success: (response) ->
        $('.modal-body-menu', currentModal).html response
        @initMenu($('#media-modal-' + $('#folder_id').val()))
    return

  openFormFolder: (event) ->
    @updateNavigation($(event.target))
    @openForm event

  openForm: (event) ->
    event.preventDefault()
    displayLoader $(".modal-body-content", @$el)
    folderName = $(".js-widget-title", @$el).text()
    domContainer = $(".modal-body-content", @$el)
    @listenToOnce(formChannel, 'formSubmit', @reloadFolder) if $(event.target).hasClass('media-modal-menu-new-folder')
    title = @getPath().join(' > ')
    $.ajax
      url: $(event.target).data('url')
      method: 'GET'
      success: (response) ->
        viewClass = appConfigurationView.getConfiguration('media', 'showMediaForm')
        new viewClass(
            html: response
            domContainer: domContainer
            title: title
          )
)
