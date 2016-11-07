###*
 * @namespace OpenOrchestra
###
window.OpenOrchestra or= {}

###*
 * @class ModalFolderFormView
###
class OpenOrchestra.ModalFolderFormView extends OrchestraView

  extendView: ['modalFolderSubmitAdmin']

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
    @options.formView = 'modal_folder_form'
    return

  ###*
   * render
  ###
  render: ->
    @setElement @renderTemplate('OpenOrchestraBackofficeBundle:BackOffice:Underscore/fullPageFormView', @options)
    @options.domContainer.html @$el
    $('.js-widget-title', @options.domContainer).html @options.title
    $('.back-to-list', @options.domContainer).remove()

jQuery ->
  appConfigurationView.setConfiguration 'modal_folder_form', 'folder', OpenOrchestra.ModalFolderFormView
