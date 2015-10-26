extendView = extendView || {}
extendView['breadcrumbAware'] =
  getPath: ->
    navNodes = $('.modal-body-menu nav .active > a').parents('li')
    pathArray = []
    $(navNodes.get().reverse()).each ->
      link = $('a:first', $(@))
      pathArray.push link.text()
      return
    pathArray
