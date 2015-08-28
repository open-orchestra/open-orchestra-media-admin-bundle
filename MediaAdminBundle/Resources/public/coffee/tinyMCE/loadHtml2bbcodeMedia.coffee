((html2bbcode) ->
  mediaTransformation =
    '<img class="tinymce-media" src="([^"]*)\\/([^"\/-]*)-([^"\/]*)" alt="([^"]*)" \\/>' : '[media type="image" host="$1" format="$2" id="$3" alt="$4"]',
    '<img class="tinymce-media" src="([^"]*)\\/([^"\/-]*)" alt="([^"]*)" \\/>' : '[media type="image" host="$1" format="original" id="$2" alt="$3"]',
    '<img class="tinmce-media" src="([^"]*)\\/([^"\/-]*)-([^"\/]*)" \\/>' : '[media type="image" host="$1" format="$2" id="$3"]',
    '<img class="tinmce-media" src="([^"]*)\\/([^"\/-]*)-([^"\/]*)" \\/>' : '[media type="image" host="$1" format="original" id="$3"]',

  html2bbcode.addTransformation mediaTransformation
) window.html2bbcode
