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

      progressText = viewContext.renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/Include/uploadProgress',
        uniqueIdentifier: file.uniqueIdentifier
      )

      $('.flow-list').append progressText
      $self = $('.flow-file-' + file.uniqueIdentifier)
      $self.find('.flow-file-name').text file.name
      $self.find('.flow-file-size').text readablizeBytes(file.size)
      $self.find('.flow-file-pause').on 'click', ->
        file.pause()
        $self.find('.flow-file-pause').hide()
        $self.find('.flow-file-resume').show()
        return
      $self.find('.flow-file-resume').on 'click', ->
        file.resume()
        $self.find('.flow-file-pause').show()
        $self.find('.flow-file-resume').hide()
        return
      $self.find('.flow-file-cancel').on 'click', ->
        file.cancel()
        $self.remove()
        return
      return

    @r.on 'filesSubmitted', (file) ->
      viewContext.r.upload()
      return

    @r.on 'fileSuccess', (file, message) ->
      $self = $('.flow-file-' + file.uniqueIdentifier)
      $self.find('.flow-file-progress').text '(' + $('#uploadCompleted').text() + ')'
      $self.find('.flow-file-pause, .flow-file-resume, .flow-file-cancel').remove()
      return

    @r.on 'fileError', (file, message) ->
      $('.flow-file-' + file.uniqueIdentifier + ' .flow-file-progress').html '(' + $('#uploadFailed').text() + message + ')'
      return

    @r.on 'fileProgress', (file) ->
      $('.flow-file-' + file.uniqueIdentifier + ' .flow-file-progress').html Math.floor(file.progress() * 100) + '% ' + readablizeBytes(file.averageSpeed) + '/s ' + secondsToStr(file.timeRemaining()) + ' ' + $('#uploadRemaining').text()
      $('.progress-bar').css width: Math.floor(viewContext.r.progress() * 100) + '%'
      return

    window.r =
      pause: ->
        viewContext.r.pause()
        $('.flow-file-resume').show()
        $('.flow-file-pause').hide()
        return
      cancel: ->
        viewContext.r.cancel()
        $('.flow-file').remove()
        return
      upload: ->
        $('.flow-file-pause').show()
        $('.flow-file-resume').hide()
        viewContext.r.resume()
        return
      flow: @r
)
