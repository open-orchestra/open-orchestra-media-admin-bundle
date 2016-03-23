###*
 * @namespace OpenOrchestra:Media
###
window.OpenOrchestra or= {}
window.OpenOrchestra.Media or= {}

###*
 * @class WidgetMediaTypeFilterView
###
class OpenOrchestra.Media.WidgetTypeFilterView extends OrchestraView
  events:
    'click ul>li>a': 'filterMedia'

  initialize: (options) ->
    @noFilterUrl = ''
    @filters = []
    @options = @reduceOption(options, [
      'viewContainer'
      'widget_index'
      'media_types_url'
    ])
    @loadTemplates [
      'OpenOrchestraMediaAdminBundle:BackOffice:Underscore/widgetMediaTypeFilter',
    ]
    return

  ###*
   * @return {this}
  ###
  render: ->
    viewContext = @
    $.ajax
      url: viewContext.options.media_types_url
      method: 'GET'
      success: (response) ->
        viewContext.noFilterUrl = response.links._no_filter
        viewContext.filters = response.media_types
        viewContext.drawWidget('')
    return

  ###*
   * @param {string} selectedFilter
  ###
  drawWidget: (selectedFilter) ->
    @setElement @renderTemplate('OpenOrchestraMediaAdminBundle:BackOffice:Underscore/widgetMediaTypeFilter',
      no_filter_url: @noFilterUrl
      filters: @filters
      selectedFilter: selectedFilter
    )
    @$el.attr('data-widget-index', @options.widget_index)
    addCustomJarvisWidget(@$el, @options.viewContainer.$el)

  ###*
   * @param {object} event jquery event
  ###
  filterMedia: (event) ->
    selectedFilter = $(event.currentTarget).data('value')
    viewContext = @

    $.ajax
      url: $(event.currentTarget).data('url')
      method: 'GET'
      success: (response) ->
        medias = new GalleryElement
        medias.set response
        viewContext.remove()
        viewContext.drawWidget(selectedFilter)
        OpenOrchestra.Media.Channel.trigger 'mediasFiltered', medias
    return
