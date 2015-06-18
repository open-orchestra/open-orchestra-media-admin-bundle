MediaFormView = OrchestraView.extend(
  events:
    'submit': 'addEventOnForm'

  initialize: (options) ->
    @options = @reduceOption(options, [
      'html'
      'title'
      'domContainer'
    ])
    @loadTemplates [
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/fullPageFormView'
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/fullPageFormView', @options)
    @options.domContainer.html @$el
    $('.js-widget-title', @options.domContainer).html @options.title
    $('.back-to-list', @options.domContainer).remove()
    $("[data-prototype]", @options.domContainer).each ->
      PO.formPrototypes.addPrototype $(this)
      return
    return

  addEventOnForm: (event) ->
    event.preventDefault()
    viewContext = @
    console.log("submit")
    $('form', @options.domContainer).ajaxSubmit
      context:
        button: $(".submit_form",event.currentTarget).parent()
      success: (response) ->
        formChannel.trigger 'formSubmit'
        viewClass = appConfigurationView.getConfiguration('media', 'showMediaForm')
        new viewClass(viewContext.addOption(
          html: response
        ))
    return
)
