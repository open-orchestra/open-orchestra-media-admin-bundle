MediaFormView = OrchestraView.extend(

  extendView : [ 'submitAdmin' ]

  initialize: (options) ->
    @options = @reduceOption(options, [
      'html'
      'title'
      'domContainer'
    ])
    @loadTemplates [
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/fullPageFormView'
    ]
    @options.formView = 'showMediaForm'
    @options.entityType = 'media'
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
)
