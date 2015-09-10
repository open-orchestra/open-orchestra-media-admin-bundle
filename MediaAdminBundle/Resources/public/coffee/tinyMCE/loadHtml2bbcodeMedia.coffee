((html2bbcode) ->
  mediaTransformation =
    '<img class="tinymce-media" src="([^"]*)\\/([^"\/-]*)" \\/>' : '[media="$2"]$1[/media]',
    '<img class="tinymce-media" src="([^"]*)\\/" \\/>' : '[media="original"]$1[/media]',

  html2bbcode.addTransformation mediaTransformation
) window.html2bbcode
