((html2bbcode) ->
  mediaTransformation =
    '<img class="tinymce-media"(.*?)style="([^"]*)"(.*?)\/>' : '<span style="$2"><img class="tinymce-media"$1$3\/></span>',
    '<img class="tinymce-media".*?data-id="([^"]*)".*?data-format="([^"]+)".*?\/>' : '[media=$2]$1[/media]',
    '<img class="tinymce-media".*?data-id="([^"]*)".*?\/>' : '[media]$1[/media]',

  html2bbcode.addTransformation mediaTransformation
) window.html2bbcode
