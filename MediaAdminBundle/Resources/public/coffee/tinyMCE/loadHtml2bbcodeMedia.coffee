((html2bbcode) ->
  mediaTransformation =
    '<img class="tinymce-media".*?data-id="([^"]*)".*?data-format="([^"]+)".*?\/>' : '[media=$2]$1[/media]',
    '<img class="tinymce-media".*?data-id="([^"]*)".*?\/>' : '[media]$1[/media]',

  html2bbcode.addTransformation mediaTransformation
) window.html2bbcode
