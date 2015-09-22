((html2bbcode) ->
  mediaTransformation =
    '<img class="tinymce-media" src="([^"]*)" alt="([^"]*)" data-id="([^"]*)" \\/>' : '[media]$3[/media]',
    '<img class="tinymce-media" src="([^"]*)" alt="([^"]*)" data-id="([^"]*)" data-format="([^"]+)" \\/>' : '[media=$4]$3[/media]',

  html2bbcode.addTransformation mediaTransformation
) window.html2bbcode
