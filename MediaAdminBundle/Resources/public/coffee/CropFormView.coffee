CropFormView = OrchestraView.extend(

  events:
    'change select#media_crop_format': 'changeView'
    'click a#crop_action_button': 'setupCrop'
    'click a#upload_action_button': 'setupOverrideForm'
    'click a#crop_button': 'cropImage'
    'submit .media_crop_form form' : 'addEventOnCropForm'
    'submit .media-override-format-form form' : 'addEventOnOverrideForm'

  initialize: (options) ->
    @options = options
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/cropFormView'
    ]
    @cropParam = []
    return

  render: ->
    @$el.html @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/cropFormView',
      media: @options.media
      listUrl: @options.listUrl
    )
    @addPreview()
    @initCrop()
    displayLoader('#alternative-loader', @$el)

  initCrop: ->
    currentView = @
    $(".media-override-format-form", @$el).hide()
    displayLoader('#selector-loader', @$el)
    $.ajax
      url: @options.media.get('links')._self_crop
      method: 'GET'
      success: (response) ->
        $('#selector-loader-container').hide()
        $('.media_crop_form', currentView.$el).html response
        if (form = $('.media_crop_form form', currentView.$el)) && form.length > 0
          activateForm(currentView, form)

  addPreview: ->
    $('.media_crop_preview', @$el).append('<img class="media_crop_original" src="' + @options.media.get('displayed_image') + '" style="max-width:600px;">')
    for thumbnail of @options.media.get('thumbnails')
      $('.media_crop_preview', @$el).append('<img class="media_crop_' + thumbnail + '" src="' + @options.media.get('thumbnails')[thumbnail] + '" style="display: none;">')
    displayLoader('#image-loader', @$el)

  changeView: (e) ->
    $('#crop-group').hide()
    $(".media-override-format-form", @$el).hide()
    @cropParam['jcrop_api'].destroy() if @cropParam['jcrop_api']?
    $('.media_crop_preview img', @$el).hide()
    format = e.currentTarget.value
    $('.superbox-current-img', @$el).append('<div id="preview-pane" style="display: none">
          <div class="preview-container">
              <img  class="jcrop-preview" alt="Preview" />
          </div>
      </div>')
    $('#preview-pane .preview-container', @$el).height($('.media_crop_' + format, @$el).height())
    $('#preview-pane .preview-container', @$el).width($('.media_crop_' + format, @$el).width())
    @showPreview(format)

  showPreview: (format) ->
    if format != ''
      $('.media_crop_' + format, @$el).show()
      $('.media_format_actions').show()
    else
      $('.media_crop_original', @$el).show()
      $('.media_format_actions').hide()

  setupCrop: (e) ->
    e.preventDefault()
    viewContext = @
    @cropParam['jcrop_api'].destroy() if @cropParam['jcrop_api']?

    $(".media-override-format-form").hide()
    $('#crop-group').show()

    @cropParam['$preview'] = $('#preview-pane', @$el)
    @cropParam['$pimg'] = $('#preview-pane .preview-container img', @$el)
    @cropParam['$pcnt'] = $('#preview-pane .preview-container', @$el)
    @cropParam['xsize'] = @cropParam['$pcnt'].width()
    @cropParam['ysize'] = @cropParam['$pcnt'].height()
    $('.superbox-current-img').Jcrop({
        onChange: @updatePreview
        onSelect: @updateCoords
        boxWidth: 600
      }, ->
        bounds = @getBounds()
        viewContext.cropParam['boundx'] = bounds[0]
        viewContext.cropParam['boundy'] = bounds[1]

        # Store the API in the jcrop_api variable
        viewContext.cropParam['jcrop_api'] = @

        # Move the preview into the jcrop container for css positioning
        viewContext.cropParam['$preview'].appendTo @ui.holder
        return
      )
    @

  setupOverrideForm: (e) ->
    e.preventDefault()
    $('#crop-group').hide()
    $(".media-override-format-form").hide()
    $('#alternative-loader-container').show()
    format = $('#media_crop_format').val()
    linkFormat = '_self_format_' + format
    $.ajax
      url: @options.media.get('links')[linkFormat]
      method: 'GET'
      success: (response) ->
        $('.media-override-format-form').html response
        $('#alternative-loader-container').hide()
        $(".media-override-format-form").show()

  cropImage: (e) ->
    e.preventDefault()
    $("#media_crop").submit()

  addEventOnCropForm: (e) ->
    e.preventDefault()
    currentView = @
    $('#crop-group').hide()
    $('.media_crop_preview img', @$el).hide()
    $('#image-loader').show()
    $(e.currentTarget).ajaxSubmit
      statusCode:
        200: () ->
          currentView.cropParam['jcrop_api'].destroy()
          currentView.refreshImages()

  addEventOnOverrideForm: (e) ->
    e.preventDefault()
    currentView = @
    $('.media-override-format-form').hide()
    $('.media_crop_preview img', @$el).hide()
    $('#image-loader').show()
    $(e.currentTarget).ajaxSubmit
      statusCode:
        200: () ->
          currentView.refreshImages()

  refreshImages: ->
    format = $('#media_crop_format').val()
    newSrc = $('.media_crop_' + format).attr('src').split('?')[0] + '?' + Math.random()
    $('.media_crop_' + format).attr 'src', newSrc
    $(".media-override-format-form").hide()
    $('#image-loader').hide()
    @showPreview(format)
)
