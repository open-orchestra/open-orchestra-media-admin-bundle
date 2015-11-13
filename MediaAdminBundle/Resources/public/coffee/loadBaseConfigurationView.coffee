jQuery ->
  baseMediaConfiguration =
    'addFolderConfigurationButton': FolderConfigurationButtonView
    'addFolderDeleteButton': FolderDeleteButtonView
    'showGalleryCollection': GalleryCollectionView
    'showMediaForm': MediaFormView
    'showMediaModal': MediaModalView
    'showGallery': GalleryView
    'showWysiwygSelect': WysiwygSelectView
    'showMetaForm': MetaFormView
    'showCropForm': CropFormView
    'uploadMedia': MediaUploadView

  $.extend true, window.appConfigurationView.baseConfigurations,baseMediaConfiguration
