CropFormView = OrchestraView.extend(

  events:
    'change select#oo_media_crop_format': 'changeView'
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
    if !@options.isModal
      OpenOrchestra.RibbonButton.ribbonFormButtonView.setFocusedView @, '.ribbon-form-button'
    else
      OpenOrchestra.RibbonButton.ribbonFormButtonView.setFocusedView @, '.modal-form-button'

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
    $('.media_crop_preview', @$el).append('<img class="media_crop_original" src="' + @options.media.get('original') + '" >')
    for alternative of @options.media.get('alternatives')
      $('.media_crop_preview', @$el).append('<img class="media_crop_' + alternative + '" src="' + @options.media.get('alternatives')[alternative] + '" style="display: none;">')
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

    #take real image size without CSS
    naturalImage = new Image();
    naturalImage.src = $("img.superbox-current-img", "#crop-group").attr("src");
    naturalWidth = naturalImage.width
    naturalHeight = naturalImage.height
    naturalImage.remove

    $('.superbox-current-img').Jcrop({
        onChange: @updatePreview
        onSelect: @updateCoords
        boxWidth: 600
        trueSize: [naturalWidth, naturalHeight]
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
    format = $('#oo_media_crop_format').val()
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
    $("#oo_media_crop").submit()

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
    currentView = @
    media = @options.media
    $.ajax
      url: appRouter.generateUrl('apiMediaEdit', mediaId: media.get('id'))
      method: 'GET'
      success: (response) ->
        media = new GalleryModel
        media.set response
        format = $('#oo_media_crop_format').val()
        newSrc = media.get('alternatives')[format]
        $('.media_crop_' + format)
          .attr('src', newSrc)
          .load ->
            $(".media-override-format-form").hide()
            $('#image-loader').hide()
            currentView.showPreview format

  updateCoords: (c) ->
    $('#oo_media_crop_x').val c.x
    $('#oo_media_crop_y').val c.y
    $('#oo_media_crop_w').val c.w
    $('#oo_media_crop_h').val c.h
)
