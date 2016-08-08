###*
 * @namespace OpenOrchestra:FormBehavior
###
window.OpenOrchestra or= {}
window.OpenOrchestra.FormBehavior or= {}

###*
 * @class MediaType
###
class OpenOrchestra.FormBehavior.MediaType extends OpenOrchestra.FormBehavior.AbstractFormBehavior

  ###*
   * activateBehaviorOnElements
   * @param {Array} elements
   * @param {Object} view
  ###
  activateBehaviorOnElements: (elements, view) ->
    $.extend true, view, extendView['orchestraMediaAbstractType'], extendView['orchestraMediaType']
    view.delegateEvents()

  ###*
   * deactivateBehaviorOnElements
  ###
  deactivateBehaviorOnElements: () ->
    return

jQuery ->
  OpenOrchestra.FormBehavior.formBehaviorLibrary.add(new OpenOrchestra.FormBehavior.MediaType('a.mediaModalOpen'))
