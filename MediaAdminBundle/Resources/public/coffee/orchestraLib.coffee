#ACTIVATE TINYMCE
callback_tinymce_init = null
tinymce_button_mediamanager = null

doCallBack = (editor, view) ->
  tinymce_button_mediamanager = (editor) ->
    $.extend true, view, extendView['orchestraMediaAbstractType'], extendView['orchestraWysiwygType']
    target = editor.id + '_modal'
    view.WysiwygTypeModal
      target: target
      input: editor.id
      url: $('#' + target).data('url')

  view.delegateEvents()
  return
