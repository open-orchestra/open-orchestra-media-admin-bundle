MediaUploadView = OrchestraView.extend(

  extendView: ['submitAdmin']

  events:
    'dragenter .flow-drop': 'dragEnter'
    'dragend .flow-drop': 'dragEnd'
    'drop .flow-drop': 'dragEnd'

  initialize: (options) ->
    @options = options
    @r = new Flow(
      target: @options.uploadUrl
      chunkSize: 1024 * 1024
      testChunks: false)
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaUploadView'
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/uploadProgress'
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaUploadView',
       listUrl: @options.listUrl
       isModal: @options.isModal
    )
    @options.domContainer.html @$el
    if !@options.isModal
      OpenOrchestra.RibbonButton.ribbonFormButtonView.setFocusedView @, '.ribbon-form-button'
    else
      OpenOrchestra.RibbonButton.ribbonFormButtonModalView.setFocusedView @, '.modal-form-button'
    $('.js-widget-title', @options.domContainer).html @options.title
    @renderSubmitFile()
    return

  dragEnter: ->
    $('.flow-drop').addClass('flow-dragover')

  dragEnd: ->
    $('.flow-drop').removeClass('flow-dragover')

  renderSubmitFile: ->
    if !@r.support
      $('.flow-error').show()
      return

    allowedMimeTypes = $('.flow-drop').data('format')

    @r.assignDrop $('.flow-drop')[0]
    @r.assignBrowse $('.flow-browse-folder')[0], true
    @r.assignBrowse $('.flow-browse')[0], false, false, {accept: allowedMimeTypes}

    viewContext = this

    @r.on 'fileAdded', (file) ->
      $('.flow-progress, .flow-list').show()
      uploadProgressViewClass = appConfigurationView.getConfiguration('upload_progress_element', 'uploadMedia')
      file.uploadProgressView = new uploadProgressViewClass(
        domContainer: $('.flow-list', @$el)
        file: file
      )
      return

    @r.on 'filesSubmitted', (file) ->
      viewContext.r.upload()
      return

    @r.on 'fileSuccess', (file, message) ->
      file.uploadProgressView.writeProgress '(' + $('#uploadCompleted').text() + ')'
      file.uploadProgressView.hideButtons();
      return

    @r.on 'fileError', (file, message) ->
      file.uploadProgressView.writeProgress '(' + $('#uploadFailed').text() + message + ')'
      return

    @r.on 'fileProgress', (file) ->
      file.uploadProgressView.writeProgress Math.floor(file.progress() * 100) + '% ' + readablizeBytes(file.averageSpeed) + '/s ' + secondsToStr(file.timeRemaining()) + ' ' + $('#uploadRemaining').text()
      $('.progress-bar', viewContext).css width: Math.floor(viewContext.r.progress() * 100) + '%'
      return
)
