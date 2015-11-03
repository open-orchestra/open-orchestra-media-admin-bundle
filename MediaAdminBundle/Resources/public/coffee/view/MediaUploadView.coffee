MediaUploadView = OrchestraView.extend(

  extendView: ['submitAdmin']

  initialize: (options) ->
    @options = options
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaUploadView'
    ]
    return

  render: ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/mediaUploadView')
    @options.domContainer.html @$el
    $('.js-widget-title', @options.domContainer).html @options.title
    @renderSubmitFile()
    return

  renderSubmitFile: ->
    r = new Flow(
      target: @options.uploadUrl
      chunkSize: 1024 * 1024
      testChunks: false)

    # Flow.js isn't supported, fall back on a different method
    if !r.support
      $('.flow-error').show()
      return

    # Show a place for dropping/selecting files
    $('.flow-drop').show()
    r.assignDrop $('.flow-drop')[0]
    r.assignBrowse $('.flow-browse')[0]
    r.assignBrowse $('.flow-browse-folder')[0], true
    r.assignBrowse $('.flow-browse-image')[0], false, false, accept: 'image/*'

    # Handle file add event
    r.on 'fileAdded', (file) ->
      # Show progress bar
      $('.flow-progress, .flow-list').show()
      # Add the file to the list
      $('.flow-list').append '<li class="flow-file flow-file-' + file.uniqueIdentifier + '">' + 'Uploading <span class="flow-file-name"></span> ' + '<span class="flow-file-size"></span> ' + '<span class="flow-file-progress"></span> ' + '<a href="" class="flow-file-download" target="_blank">' + 'Download' + '</a> ' + '<span class="flow-file-pause">' + ' <img src="/img/flow_js/pause.png" title="Pause upload" />' + '</span>' + '<span class="flow-file-resume">' + ' <img src="/img/flow_js/resume.png" title="Resume upload" />' + '</span>' + '<span class="flow-file-cancel">' + ' <img src="/img/flow_js/cancel.png" title="Cancel upload" />' + '</span>'
      $self = $('.flow-file-' + file.uniqueIdentifier)
      $self.find('.flow-file-name').text file.name
      $self.find('.flow-file-size').text readablizeBytes(file.size)
      $self.find('.flow-file-download').attr('href', '/download/' + file.uniqueIdentifier).hide()
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

    r.on 'filesSubmitted', (file) ->
      r.upload()
      return

    r.on 'complete', ->
      # Hide pause/resume when the upload has completed
      $('.flow-progress .progress-resume-link, .flow-progress .progress-pause-link').hide()
      return

    r.on 'fileSuccess', (file, message) ->
      $self = $('.flow-file-' + file.uniqueIdentifier)
      # Reflect that the file upload has completed
      $self.find('.flow-file-progress').text '(completed)'
      $self.find('.flow-file-pause, .flow-file-resume').remove()
      $self.find('.flow-file-download').attr('href', '/download/' + file.uniqueIdentifier).show()
      return

    r.on 'fileError', (file, message) ->
      # Reflect that the file upload has resulted in error
      $('.flow-file-' + file.uniqueIdentifier + ' .flow-file-progress').html '(file could not be uploaded: ' + message + ')'
      return

    r.on 'fileProgress', (file) ->
      # Handle progress for both the file and the overall upload
      $('.flow-file-' + file.uniqueIdentifier + ' .flow-file-progress').html Math.floor(file.progress() * 100) + '% ' + readablizeBytes(file.averageSpeed) + '/s ' + secondsToStr(file.timeRemaining()) + ' remaining'
      $('.progress-bar').css width: Math.floor(r.progress() * 100) + '%'
      return

    r.on 'uploadStart', ->
      # Show pause, hide resume
      $('.flow-progress .progress-resume-link').hide()
      $('.flow-progress .progress-pause-link').show()
      return

#    r.on 'catchAll', ->
#      console.log.apply console, arguments
#      return

    window.r =
      pause: ->
        r.pause()
        # Show resume, hide pause
        $('.flow-file-resume').show()
        $('.flow-file-pause').hide()
        $('.flow-progress .progress-resume-link').show()
        $('.flow-progress .progress-pause-link').hide()
        return
      cancel: ->
        r.cancel()
        $('.flow-file').remove()
        return
      upload: ->
        $('.flow-file-pause').show()
        $('.flow-file-resume').hide()
        r.resume()
        return
      flow: r
)