###*
 * @channel Media
 * Event available
 *  - mediasFiltered(mediaCollection) get a media collection filtered
###
(($, OpenOrchestra) ->

  OpenOrchestra.Media = {} if not OpenOrchestra.Media?
  OpenOrchestra.Media.Channel = new (Backbone.Wreqr.EventAggregator)

) jQuery,
  window.OpenOrchestra = window.OpenOrchestra or {}
