###*
 * @namespace OpenOrchestra:Upload
###
window.OpenOrchestra or= {}
window.OpenOrchestra.Upload or= {}

###*
 * @class UploadProgressView
###

class OpenOrchestra.Upload.UploadProgressView extends OrchestraView

  events:
    'click .flow-file-pause': 'filePause'
    'click .flow-file-resume': 'fileResume'
    'click .flow-file-cancel': 'fileCancel'

  ###*
   * initialize
   * @param {Object} options
  ###
  initialize: (options) ->
    @options = options
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/uploadProgress'
    ]
    return

  ###*
   * render
  ###
  render: ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/uploadProgress',
       name: @options.file.name
       size: readablizeBytes(@options.file.size)
    )
    @options.domContainer.append @$el

  ###*
   * filePause
  ###
  filePause: ->
    @options.file.pause()
    $('.flow-file-pause', @$el).hide()
    $('.flow-file-resume', @$el).show()
    return

  ###*
   * fileResume
  ###
  fileResume: ->
    @options.file.resume()
    $('.flow-file-pause', @$el).show()
    $('.flow-file-resume', @$el).hide()
    return

  ###*
   * fileCancel
  ###
  fileCancel: ->
    @options.file.cancel()
    @$el.remove()
    return

  ###*
   * writeProgress
   * @param {String} message
  ###
  writeProgress: (message) ->
    $('.flow-file-progress', @$el).text message
    return

  ###*
   * hideButtons
  ###
  hideButtons: ->
    $('.flow-file-pause, .flow-file-resume, .flow-file-cancel', @$el).remove()
    return
