###*
 * @namespace OpenOrchestra
###
window.OpenOrchestra or= {}

###*
 * @class ModalFolderFormView
###
class OpenOrchestra.ModalFolderFormView extends OrchestraView

  events:
    'click .submit_form': 'addEventOnSave'

  ###*
   * @param {Object} options
  ###
  initialize: (options) ->
    ###*
     * {String} html
     * {String} title
     * {Object} domContainer
    ###
    @options = @reduceOption(options, [
      'html'
      'title'
      'domContainer'
    ])
    @loadTemplates [
      'OpenOrchestraBackofficeBundle:BackOffice:Underscore/fullPageFormView'
    ]
    @options.entityType = 'folder'
    return

  ###*
   * render
  ###
  render: ->
    @setElement @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/fullPageFormView', @options)
    @options.domContainer.html @$el
    $('.js-widget-title', @options.domContainer).html @options.title
    $('.back-to-list', @options.domContainer).remove()

  ###*
   * @param {Object} event
  ###
  addEventOnSave: (event) ->
    displayLoader @$el
    viewContext = @
    form = $(event.target).closest('form')
    if !form.hasClass('HTML5validation')
      form.addClass('HTML5validation')
      form.submit ->
        event.preventDefault()
        form.ajaxSubmit
          url: form.data('action')
          statusCode:
            201: (response,  status, xhr) ->
              formChannel.trigger('element-created', response, xhr.getResponseHeader('document-id'))
            200: (response) ->
              window.OpenOrchestra.FormBehavior.channel.trigger 'deactivate', viewContext, form
              widgetChannel.trigger 'form-error', viewContext
              viewClass = appConfigurationView.getConfiguration('modal_folder_form', 'folder')
              new viewClass(viewContext.addOption(
                html: response
                submitted: true
              ))
        false
    return

jQuery ->
  appConfigurationView.setConfiguration 'modal_folder_form', 'folder', OpenOrchestra.ModalFolderFormView
