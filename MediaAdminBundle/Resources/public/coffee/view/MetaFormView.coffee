MetaFormView = OrchestraView.extend(

  extendView : [ 'submitAdmin' ]

  className : 'media_meta_form'

  initialize: (options) ->
    @options = options
    @options.formView = 'showMetaForm'
    @options.entityType = 'media'
    @options.domContainer = @$el if !@options.submitted
    @loadTemplates [
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/backToList'
    ]
    return

  render: ->
    if !@options.submitted
      @initMetaForm()
    else
      @setElement @options.html
      @options.domContainer.html @$el
      activateForm(@, @options.domContainer)
      @options.domContainer.append @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/backToList',
        listUrl: @options.listUrl
      )

  initMetaForm : ->
    currentView = @
    $.ajax
      url: @options.media.get('links')._self_meta
      method: 'GET'
      success: (response) ->
        currentView.$el.html response
        if (form = $('form', currentView.$el)) && form.length > 0
          activateForm(currentView, form)
        currentView.$el.append currentView.renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/backToList',
          listUrl: currentView.options.listUrl
        )
        $('.submit_form.btn-in-ribbon',currentView.$el).removeClass('btn-in-ribbon')
        OpenOrchestra.RibbonButton.ribbonFormButtonView.setFocusedView @, '.ribbon-form-button'
)
