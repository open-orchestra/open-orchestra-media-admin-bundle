((bbcode2html) ->
  mediaTransformation =
    '\\[media="([^"\\/-]*)"\\]([^"]*)\\[\\/img\\]' : '<img class="tinymce-media" src="$2/$1" />',

  bbcode2html.addTransformation mediaTransformation
) window.bbcode2html
