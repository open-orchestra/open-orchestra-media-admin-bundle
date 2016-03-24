extendView = extendView || {}
extendView['breadcrumbAware'] =
  getPath: ->
    navNodes = $('.modal-body-menu nav a.active').parents('li')
    pathArray = []
    $(navNodes.get().reverse()).each ->
      link = $('a:first > span', $(this))
      pathArray.push link.text()
      return
    pathArray
