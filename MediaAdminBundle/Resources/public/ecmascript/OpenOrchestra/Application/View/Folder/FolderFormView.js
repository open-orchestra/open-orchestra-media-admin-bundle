import AbstractFormView     from 'OpenOrchestra/Service/Form/View/AbstractFormView'
import Application          from 'OpenOrchestra/Application/Application'
import Folder               from 'OpenOrchestra/Application/Model/Folder/Folder'
import FormViewButtonsMixin from 'OpenOrchestra/Service/Form/Mixin/FormViewButtonsMixin'
import ApplicationError     from 'OpenOrchestra/Service/Error/ApplicationError'

/**
 * @class FolderFormView
 */
class FolderFormView extends mix(AbstractFormView).with(FormViewButtonsMixin)
{
    /**
     * Initialize
     * @param {Form}   form
     * @param {string} folderId
     */
    initialize({form, folderId = null}) {
        super.initialize({form : form});
        this._folderId = folderId;
    }

    /**
     * @inheritdoc
     */
    render() {
        let title = $("#oo_folder_names_" + Application.getContext().get('language'), this._form.$form).val()
        if (null === this._folderId) {
            title = Translator.trans('open_orchestra_media_admin.table.folder.new');
        }
        let template = this._renderTemplate('Folder/folderEditView', {
            title: title
        });
        this.$el.html(template);
        this._$formRegion = $('.form-edit', this.$el);
        super.render();

        return this;
    }

    /**
     * Delete content type
     */
    _deleteElement() {
        if (null === this._folderId) {
            throw new ApplicationError('Invalid folderId');
        }
        let folder = new Folder({'id': this._folderId});
        folder.destroy({
            success: () => {
                let url = Backbone.history.generateUrl('listFolders');
                Backbone.history.navigate(url, true);
            }
        });
    }

    /**
     * Redirect to new workflow profile view
     *
     * @param {mixed}  data
     * @param {string} textStatus
     * @param {object} jqXHR
     * @private
     */
    _redirectEditElement(data, textStatus, jqXHR) {
        let folderId = jqXHR.getResponseHeader('folderId');
        let url = Backbone.history.generateUrl('editFolder', {
            folderId: folderId
        });
        Backbone.Events.trigger('form:deactivate', this);
        Backbone.history.navigate(url, true);
    }
}

export default FolderFormView;
