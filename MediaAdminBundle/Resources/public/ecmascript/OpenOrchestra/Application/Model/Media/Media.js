import OrchestraModel from '../OrchestraModel'

/**
 * @class Media
 */
class Media extends OrchestraModel
{
    /**
     * @inheritdoc
     */
    _getSyncUrl(method, options) {
        let urlParameter = options.urlParameter || {};
        switch (method) {
            case "delete":
                urlParameter.groupId = this.get('id');
                return Routing.generate('open_orchestra_api_media_delete', urlParameter);
        }
    }
}

export default Media
