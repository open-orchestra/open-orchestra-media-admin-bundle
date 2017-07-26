import OrchestraCollection from 'OpenOrchestra/Application/Collection/OrchestraCollection'
import Site                from 'OpenOrchestra/Application/Model/Site/Site'

/**
 * @class SitesShareMediaLibrary
 */
class SitesShareMediaLibrary extends OrchestraCollection
{
    /**
     * Pre initialize
     */
    preinitialize() {
        this.model = Site;
    }

    /**
     * @inheritdoc
     */
    parse(response) {
        if (response.hasOwnProperty('sites')) {
            return response.sites
        }
    }

    /**
     * @inheritdoc
     */
    _getSyncUrl(method) {
        switch (method) {
            case "read":
                return Routing.generate('media_library_sharing_list_sites');
        }
    }
}

export default SitesShareMediaLibrary
