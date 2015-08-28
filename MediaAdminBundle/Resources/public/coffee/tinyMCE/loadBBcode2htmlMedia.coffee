((bbcode2html) ->
  mediaTransformation =
    '\\[media type="image" host="([^"]*)" format="([^"\\/-]*)" id="([^"\\/]*)" alt="([^"]*)"\\]' : '<img class="tinymce-media" src="$1/$2-$3" alt="$4" />',
    '\\[media type="image" host="([^"]*)" format="([^"\\/-]*)" id="([^"\\/]*)"\\]' : '<img class="tinymce-media" src="$1/$2-$3" />',

  bbcode2html.addTransformation mediaTransformation
) window.bbcode2html
