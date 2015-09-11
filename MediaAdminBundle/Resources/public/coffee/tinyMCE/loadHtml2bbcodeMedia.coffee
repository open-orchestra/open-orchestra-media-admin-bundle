((html2bbcode) ->
  mediaTransformation =
    '<img class="tinymce-media" src="([^"]*)" alt="" data-id="([^"]*)" data-format="([^"]*)" \\/>' : '[media=$3]$2[/media]',

  html2bbcode.addTransformation mediaTransformation
) window.html2bbcode
