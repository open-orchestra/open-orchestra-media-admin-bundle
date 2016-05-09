((html2bbcode) ->
  mediaTransformation =
    '<img class="tinymce-media".*?style="([^"]*)".*?data-id="(.*?)".*?data-format="(.*?)".*?\/>' : '[media={"format":"$3","style":"$1"}]$2[/media]',
    '<img class="tinymce-media".*?data-id="(.*?)".*?data-format="(.*?)".*?\/>' : '[media={"format":"$2"}]$1[/media]',
    '<img class="tinymce-media".*?data-id="(.*?)".*?\/>' : '[media]$1[/media]',

  html2bbcode.addTransformation mediaTransformation
) window.html2bbcode
