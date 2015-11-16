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

#FLOW JS
readablizeBytes = (bytes) ->
  s = [
    'bytes'
    'kB'
    'MB'
    'GB'
    'TB'
    'PB'
  ]
  e = Math.floor(Math.log(bytes) / Math.log(1024))
  (bytes / Math.pow(1024, e)).toFixed(2) + ' ' + s[e]

secondsToStr = (temp) ->
  years = Math.floor(temp / 31536000)

  numberEnding = (number) ->
    if number > 1 then 's' else ''

  if years
    return years + ' year' + numberEnding(years)
  days = Math.floor((temp %= 31536000) / 86400)
  if days
    return days + ' day' + numberEnding(days)
  hours = Math.floor((temp %= 86400) / 3600)
  if hours
    return hours + ' hour' + numberEnding(hours)
  minutes = Math.floor((temp %= 3600) / 60)
  if minutes
    return minutes + ' minute' + numberEnding(minutes)
  seconds = temp % 60
  seconds + ' second' + numberEnding(seconds)
