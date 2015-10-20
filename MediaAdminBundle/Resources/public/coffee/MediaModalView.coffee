formChannel = new (Backbone.Wreqr.EventAggregator)

currentModal = null

MediaModalView = OrchestraView.extend(
  events:
    'click .mediaModalClose': 'closeModal'
    'click .media-modal-menu-folder' : 'showFolder'
    'click .ajax-add': 'openForm'
    'click .media-modal-menu-new-folder' : 'openForm'

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
    opts =
      accordion: true
      speed: $.menu_speed
      closedSign: "<em class=\"fa fa-plus-square-o\"></em>"
      openedSign: "<em class=\"fa fa-minus-square-o\"></em>"
    $(@el).jarvismenu opts

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

  closeModal: ->
    if currentModal
      currentModal.modal "hide"

  showFolder: (event) ->
    displayLoader $(".modal-body-content", @$el)
    GalleryLoad $(event.target).attr('id'), @options.galleryView, $(".modal-body-content", @$el)

  reloadFolder: ->
    displayLoader $('.modal-body-menu', @$el)
    displayMenu()
    $.ajax
      url: @options.url
      method: 'GET'
      success: (response) ->
        $('.modal-body-menu', currentModal).html response
    return

  openForm: (event) ->
    event.preventDefault()
    displayLoader $(".modal-body-content", @$el)
    folderName = $(".js-widget-title", @$el).text()
    domContainer = $(".modal-body-content", @$el)
    @listenToOnce(formChannel, 'formSubmit', @reloadFolder) if $(event.target).hasClass('media-modal-menu-new-folder')
    $.ajax
      url: $(event.target).data('url')
      method: 'GET'
      success: (response) ->
        viewClass = appConfigurationView.getConfiguration('media', 'showMediaForm')
        new viewClass(
            html: response
            domContainer: domContainer
            title: $.trim(folderName)
          )
)
