extendView = extendView || {}
extendView['folderSubmitAdmin'] = {
  events:
    'click .submit_form': 'addEventOnSave'

  addEventOnSave: (event) ->
    viewContext = @
    viewClass = appConfigurationView.getConfiguration(viewContext.options.entityType, viewContext.options.formView)
    form = $(event.target).closest('form')
    if form.length == 0 && (clone = $(event.target).data('clone'))
      $('#' + clone).click()
    else
      if !form.hasClass('HTML5validation')
        form.addClass('HTML5validation')
        form.submit ->
          event.preventDefault()
          form.ajaxSubmit
            url: form.data('action')
            statusCode:
              201: (response,  status, xhr) ->
                viewContext.options.domContainer.modal 'hide'
                launchNotification('success', $(response).text())
                widgetChannel.trigger 'element-created', viewContext
                listUrl = appRouter.generateUrl('listFolder',
                  'folderId': xhr.getResponseHeader('document-id')
                )
                refreshMenu(listUrl)
              200: (response) ->
                window.OpenOrchestra.FormBehavior.channel.trigger 'deactivate', viewContext, form
                widgetChannel.trigger 'form-error', viewContext

                new viewClass(viewContext.addOption(
                  html: response
                  submitted: true
                ))
                $(document).scrollTop 0
              403: (response) ->
                displayRoute = OpenOrchestra.ForbiddenAccessRedirection[Backbone.history.fragment]
                if typeof displayRoute != 'undefined'
                  Backbone.history.navigate(displayRoute, {trigger: true})
          false
    return
}
