<?php

namespace OpenOrchestra\MediaAdminModelBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\MongoDB\Database;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Media migration class
 */
class Version20160411173754 extends AbstractMigration implements ContainerAwareInterface
{
    const MEDIA_TYPE_IMAGE = 'image';
    const MEDIA_TYPE_AUDIO = 'audio';
    const MEDIA_TYPE_DEFAULT = 'default';
    const THUMBNAIL_IMAGE_PREFIX = 'media_thumbnail-';
    const THUMBNAIL_AUDIO = 'orchestra-media-thumbnail-audio.png';
    const MEDIA_PREFIX = 'media-';
    const MEDIA_CHOICE_TYPE = 'orchestra_media';
    const TINYMCE_TYPE = 'tinymce';
    const ALTERNATIVES_MAPPING = array(
        'fixed_height' => 'max_height-',
        'fixed_width' => 'max_width-',
        'rectangle' => 'rectangle-'
    );
    const ALTERNATIVES_RENAMING = array(
        'max_height' => 'fixed_height',
        'max_width' => 'fixed_width'
    );

    private $container;
    protected $configuration;

    /**
     * Set the container
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return "Update media documents from 1.0.3 (or 1.1-alpha4) to 1.1" . PHP_EOL;
    }

    /**
     * Update the database
     *
     * @param Database $db
     */
    public function up(Database $db)
    {
        $this->loadConfiguration();
        $this->updateMedias($db);
        $this->updateContents($db);
        $this->updateNodes($db);
    }

    /**
     * Revert the database
     *
     * @param Database $db
     */
    public function down(Database $db)
    {
        $this->loadConfiguration();
        $this->revertNodes($db);
        $this->revertContents($db);
        $this->revertMedias($db);
    }

    /**
     * Load the configuration file
     */
    protected function loadConfiguration()
    {
        $yamlParser = new YamlParser();

        $configurationFilePath =
            $this->container->getParameter('kernel.root_dir') . '/config/media_migration.yml';

        if (is_file($configurationFilePath)) {
            $this->configuration =
                $yamlParser->parse(file_get_contents($configurationFilePath));
        } else {
            $migrationDir = $this->container->getParameter('mongo_db_migrations.dir_name');
            $this->configuration = $yamlParser->parse(file_get_contents($migrationDir . '/config/media.yml'));
        }
var_dump($this->configuration);exit;

    }

    /**
     * Update media documents
     *
     * @param Database $db
     */
    protected function updateMedias(Database $db)
    {
        $this->createMediaType($db);
        $this->updateThumbnail($db);
        $this->createAlternatives($db);
    }

    /**
     * Create mediaType attribute
     *
     * @param Database $db
     */
    protected function createMediaType(Database $db)
    {
        foreach ($this->configuration['media_types'] as $mimeType => $mediaType) {
            $db->execute($this->getCreateMediaTypeRequest($mimeType, $mediaType));
        }

        $db->execute($this->getCreateDefaultMediaTypeRequest());
    }

    /**
     * Update thumbnail attribute
     *
     * @param Database $db
     */
    protected function updateThumbnail(Database $db)
    {
        $db->execute($this->getUpdateImageThumbnailRequest());
        $db->execute($this->getUpdateAudioThumbnailRequest());
    }

    /**
     * Create alternatives attribute
     *
     * @param Database $db
     */
    protected function createAlternatives(Database $db)
    {
        $db->execute($this->getCreateAlternativesRequest());
    }

    /**
     * Update content documents
     *
     * @param Database $db
     */
    protected function updateContents(Database $db)
    {
        foreach ($this->configuration['contents']['orchestra_media'] as $contentType => $attributes) {
            $db->execute($this->getUpdateContentsRequest($contentType, $attributes));
        }

        foreach ($this->configuration['contents']['tinymce'] as $contentType => $attributes) {
            $db->execute($this->getUpdateTinyMceInContentRequest($contentType, $attributes));
        }
    }

    /**
     * Update node documents
     *
     * @param Database $db
     */
    protected function updateNodes(Database $db)
    {
        foreach ($this->configuration['blocks']['orchestra_media'] as $blockType => $attributes) {
            $db->execute($this->getUpdateNodesRequest($blockType, $attributes));
        }

        foreach ($this->configuration['blocks']['tinymce'] as $blockType => $attributes) {
             $db->execute($this->getUpdateTinyMceInNodeRequest($blockType, $attributes));
        }
    }

    /**
     * Revert node documents
     *
     * @param Database $db
     */
    protected function revertNodes(Database $db)
    {
        foreach ($this->configuration['blocks']['tinymce'] as $blockType => $attributes) {
            $db->execute($this->getRevertTinyMceInNodeRequest($blockType, $attributes));
        }

        foreach ($this->configuration['blocks']['orchestra_media'] as $blockType => $attributes) {
            $db->execute($this->getRevertNodesRequest($blockType, $attributes));
        }
    }

    /**
     * Revert content documents
     *
     * @param Database $db
     */
    protected function revertContents(Database $db)
    {
        foreach ($this->configuration['contents']['tinymce'] as $contentType => $attributes) {
            $db->execute($this->getRevertTinyMceInContentRequest($contentType, $attributes));
        }

        foreach ($this->configuration['contents']['orchestra_media'] as $contentType => $attributes) {
            $db->execute($this->getRevertContentsRequest($contentType, $attributes));
        }
    }

    /**
     * Revert media documents
     *
     * @param Database $db
     */
    protected function revertMedias(Database $db)
    {
        $db->execute($this->getRevertAlternativesRequest());
        $db->execute($this->getRevertAudioThumbnailRequest());
        $db->execute($this->getRevertImageThumbnailRequest());
        $db->execute($this->getRevertMediaTypeRequest());
    }

    /**
     * Get the request to create the mediaType attribute
     *
     * @param string $mimeType
     * @param string $mediaType
     *
     * @return string
     */
    protected function getCreateMediaTypeRequest($mimeType, $mediaType)
    {
        $filters = '
            {
                "mimeType": /' . $mimeType . '/,
                "mediaType": {$exists: false}
            }';

        return '
            db.media.find(' . $filters . ').forEach(function(item) {
                item.mediaType = "' . $mediaType . '";
                db.media.update({_id: item._id}, item);
            });';
    }

    /**
     * Get the request to create the mediaType attribute with 'default' value
     *
     * @return string
     */
    protected function getCreateDefaultMediaTypeRequest()
    {
        $filters = '
            {
                "mediaType": {$exists: false}
            }';

        return '
            db.media.find(' . $filters . ').forEach(function(item) {
                item.mediaType = "' . self::MEDIA_TYPE_DEFAULT . '";
                db.media.update({_id: item._id}, item);
            });';
    }

    /**
     * Get the request to update the thumbnail attribute of image media
     *
     * @return string
     */
    protected function getUpdateImageThumbnailRequest()
    {
        $filters = '
            {
                "mediaType": "' . self::MEDIA_TYPE_IMAGE . '",
                "alternatives": {$exists: false}
            }';

        return '
            db.media.find(' . $filters . ').snapshot().forEach(function(item) {
                item.thumbnail = "' . self::THUMBNAIL_IMAGE_PREFIX . '" + item.thumbnail;
                db.media.update({_id: item._id}, item);
            });';
    }

    /**
     * Get the request to update the thumbnail attribute of audio media
     *
     * @return string
     */
    protected function getUpdateAudioThumbnailRequest()
    {
        $filters = '
            {
                "mediaType": "' . self::MEDIA_TYPE_AUDIO . '",
                "thumbnail": {$exists: false}
            }';

        return '
            db.media.find(' . $filters . ').snapshot().forEach(function(item) {
                item.thumbnail = "' . self::THUMBNAIL_AUDIO . '";
                db.media.update({_id: item._id}, item);
            });';
    }

    /**
     * Get the request to create the alternatives attribute
     *
     * @return string
     */
    protected function getCreateAlternativesRequest()
    {
        $filters = '
            {
                "mediaType": "' . self::MEDIA_TYPE_IMAGE . '",
                "alternatives": {$exists: false}
            }';

        $request = '
            db.media.find(' . $filters . ').forEach(function(item) {
                item.alternatives = {};';

        foreach (self::ALTERNATIVES_MAPPING as $alternativeKey => $filePrefix) {
            $request .= '
                item.alternatives["' . $alternativeKey . '"] = "' . $filePrefix . '" + item.filesystemName;';
        }

        $request .= '
                db.media.update({_id: item._id}, item);
            });';

        return $request;
    }

    /**
     * Get the request to update the contents by redefining the media id storage
     *
     * @param array contentType
     * @param array attributes
     *
     * @return string
     */
    protected function getUpdateContentsRequest(array $contentType, array $attributes)
    {
        $filters = '
            {
                "contentType": "' . $contentType . '"
            }';

        $query = '
            db.content.find(' . $filters . ').forEach(function(item) {';

        foreach ($attributes as $attributeName) {
            $query .= '
                if (
                    typeof item.attributes.' . $attributeName . ' != undefined
                    && item.attributes.' . $attributeName . '.type == "' . self::MEDIA_CHOICE_TYPE . '"
                ) {
                    mediaId = item.attributes.' . $attributeName . '.value;
                    if (mediaId.indexOf("' . self::MEDIA_PREFIX . '") == 0) {
                        mediaId = mediaId.replace("' . self::MEDIA_PREFIX . '", "");
                    }

                    item.attributes.' . $attributeName . '.value = {
                        "id": mediaId,
                        "format": ""
                    };

                    item.attributes.' . $attributeName . '.stringValue = "<ul><li>" + mediaId + "</li><li></li><ul/>";
                }';
        }

        $query .= '
                db.content.update({_id: item._id}, item);
            });';

        return $query;
    }

    /**
     * Get the request to update the media format in bbcode located in tinymce from contents
     *
     * @param array contentType
     * @param array attributes
     *
     * @return string
     */
    protected function getUpdateTinyMceInContentRequest(array $contentType, array $attributes)
    {
        $filters = '
            {
                "contentType": "' . $contentType . '"
            }';

        $query = '
            db.content.find(' . $filters . ').forEach(function(item) {';

        foreach ($attributes as $attributeName) {
            $query .= '
                if (
                    typeof item.attributes.' . $attributeName . ' != undefined
                    && item.attributes.' . $attributeName . '.type == "' . self::TINYMCE_TYPE . '"
                ) {
                    tinymceValue = item.attributes.' . $attributeName . '.value;';

            foreach (self::ALTERNATIVES_RENAMING as $oldAlternativeName => $newAlternativeName) {
                $query .= '
                    tinymceValue = tinymceValue.replace("[media=' . $oldAlternativeName . ']", "[media=' . $newAlternativeName . ']");';
            }

        $query .= '
                    item.attributes.' . $attributeName . '.value = tinymceValue;
                    item.attributes.' . $attributeName . '.stringValue = tinymceValue;
                }

                db.content.update({_id: item._id}, item);
            });';
        }

        return $query;
    }

    /**
     * Get the request to update the nodes by redefining the media id storage
     *
     * @param array blockType
     * @param array attributes
     *
     * @return string
     */
    protected function getUpdateNodesRequest(array $blockType, array $attributes)
    {
        $filters = '
            {
                "blocks": {
                    $elemMatch: {"component": "' . $blockType . '"}
                }
            }';

        $query = '
            db.node.find(' . $filters . ').forEach(function(item) {

                function formatMediaId(mediaId) {
                    if (mediaId.indexOf("' . self::MEDIA_PREFIX . '") == 0) {
                        mediaId = mediaId.replace("' . self::MEDIA_PREFIX . '", "");
                    }
                    return {
                        "id": mediaId,
                        "format": ""
                    };
                }

                blocks = item.blocks;

                for (var i =  0; i < blocks.length; i++) {
                    if (blocks[i]["component"] == "' . $blockType . '") {';

        foreach ($attributes as $attributeName) {
            $query .= '
                        mediaAttribute = blocks[i]["attributes"]["' . $attributeName . '"];
                        if (typeof mediaAttribute != undefined) {
                            if (typeof mediaAttribute == "string") {
                                mediaAttribute = formatMediaId(mediaAttribute);
                            } else if (mediaAttribute.constructor === Array) {
                                for (var j = 0; j < mediaAttribute.length; j++) {
                                    if (typeof mediaAttribute[j] == "string") {
                                        mediaAttribute[j] = formatMediaId(mediaAttribute[j]);
                                    }
                                }
                            }
                        }
                        blocks[i]["attributes"]["' . $attributeName . '"] = mediaAttribute;';
        }

        $query .= '
                    }
                }
                item.blocks = blocks;
                db.node.update({_id: item._id}, item);
            });';

        return $query;
    }

    /**
     * Get the request to update the tinymce used in nodes
     *
     * @param array blockType
     * @param array attributes
     *
     * @return string
     */
    protected function getUpdateTinyMceInNodeRequest(array $blockType, array $attributes)
    {
        $filters = '
            {
                "blocks": {
                    $elemMatch: {"component": "' . $blockType . '"}
                }
            }';

        $query = '
            db.node.find(' . $filters . ').forEach(function(item) {
                blocks = item.blocks;

                for (var i =  0; i < blocks.length; i++) {
                    if (blocks[i]["component"] == "' . $blockType . '") {';

        foreach ($attributes as $attributeName) {
            $query .= '
                        tinymceAttribute = blocks[i]["attributes"]["' . $attributeName . '"];
                        if (typeof tinymceAttribute != undefined) {
                            if (typeof tinymceAttribute == "string") {';

            foreach (self::ALTERNATIVES_RENAMING as $oldAlternativeName => $newAlternativeName) {
                $query .= '
                                tinymceAttribute = tinymceAttribute.replace("[media=' . $oldAlternativeName . ']", "[media=' . $newAlternativeName . ']");';
            }

            $query .= '
                                blocks[i]["attributes"]["' . $attributeName . '"] = tinymceAttribute;
                            }
                        }';
        }

        $query .= '
                    }
                }

                item.blocks = blocks;
                db.node.update({_id: item._id}, item);
            });';

        return $query;
    }

    /**
     * Get the request to revert the tinymce used in nodes
     *
     * @param array blockType
     * @param array attributes
     *
     * @return string
     */
    protected function getRevertTinyMceInNodeRequest(array $blockType, array $attributes)
    {
        $filters = '
            {
                "blocks": {
                    $elemMatch: {"component": "' . $blockType . '"}
                }
            }';

        $query = '
            db.node.find(' . $filters . ').forEach(function(item) {
                blocks = item.blocks;

                for (var i =  0; i < blocks.length; i++) {
                    if (blocks[i]["component"] == "' . $blockType . '") {';

        foreach ($attributes as $attributeName) {
            $query .= '
                        tinymceAttribute = blocks[i]["attributes"]["' . $attributeName . '"];
                        if (typeof tinymceAttribute != undefined) {
                            if (typeof tinymceAttribute == "string") {';

            foreach (self::ALTERNATIVES_RENAMING as $oldAlternativeName => $newAlternativeName) {
                $query .= '
                                tinymceAttribute = tinymceAttribute.replace("[media=' . $newAlternativeName . ']", "[media=' . $oldAlternativeName . ']");';
            }

            $query .= '
                                blocks[i]["attributes"]["' . $attributeName . '"] = tinymceAttribute;
                            }
                        }';
        }

        $query .= '
                    }
                }

                item.blocks = blocks;
                db.node.update({_id: item._id}, item);
            });';

        return $query;
    }

    /**
     * Get the request to revert the nodes
     *
     * @param array blockType
     * @param array attributes
     *
     * @return string
     */
    protected function getRevertNodesRequest(array $blockType, array $attributes)
    {
        $filters = '
            {
                "blocks": {
                    $elemMatch: {"component": "' . $blockType . '"}
                }
            }';

        $query = '
            db.node.find(' . $filters . ').forEach(function(item) {
                blocks = item.blocks;

                for (var i =  0; i < blocks.length; i++) {
                    if (blocks[i]["component"] == "' . $blockType . '") {';

        foreach ($attributes as $attributeName) {
            $query .= '
                        mediaAttribute = blocks[i]["attributes"]["' . $attributeName . '"];
                        if (typeof mediaAttribute != undefined) {
                            if (mediaAttribute["id"] != undefined && mediaAttribute["format"] != undefined) {
                                mediaAttribute = "' . self::MEDIA_PREFIX . '" + mediaAttribute["id"];
                            } else if (mediaAttribute.constructor === Array) {
                                for (var j = 0; j < mediaAttribute.length; j++) {
                                    if (mediaAttribute[j]["id"] != undefined && mediaAttribute[j]["format"] != undefined) {
                                        mediaAttribute[j] = "' . self::MEDIA_PREFIX . '" + mediaAttribute[j]["id"];
                                    }
                                }
                            }
                        }
                        blocks[i]["attributes"]["' . $attributeName . '"] = mediaAttribute;';
        }

        $query .= '
                    }
                }
                item.blocks = blocks;
                db.node.update({_id: item._id}, item);
            });';

        return $query;
    }

    /**
     * Get the request to revert the media format in bbcode located in tinymce from contents
     *
     * @param array contentType
     * @param array attributes
     *
     * @return string
     */
    protected function getRevertTinyMceInContentRequest(array $contentType, array $attributes)
    {
        $filters = '
            {
                "contentType": "' . $contentType . '"
            }';

        $query = '
            db.content.find(' . $filters . ').forEach(function(item) {';

        foreach ($attributes as $attributeName) {
            $query .= '
                if (
                    typeof item.attributes.' . $attributeName . ' != undefined
                    && item.attributes.' . $attributeName . '.type == "' . self::TINYMCE_TYPE . '"
                ) {
                    tinymceValue = item.attributes.' . $attributeName . '.value;';

            foreach (self::ALTERNATIVES_RENAMING as $oldAlternativeName => $newAlternativeName) {
                $query .= '
                    tinymceValue = tinymceValue.replace("[media=' . $newAlternativeName . ']", "[media=' . $oldAlternativeName . ']");';
            }

            $query .= '
                    item.attributes.' . $attributeName . '.value = tinymceValue;
                    item.attributes.' . $attributeName . '.stringValue = tinymceValue;
                }

                db.content.update({_id: item._id}, item);
            });';
        }

        return $query;
    }

    /**
     * Get the request to revert the contents
     *
     * @param array contentType
     * @param array attributes
     *
     * @return string
     */
    protected function getRevertContentsRequest(array $contentType, array $attributes)
    {
        $filters = '
            {
                "contentType": "' . $contentType . '"
            }';

        $query = '
            db.content.find(' . $filters . ').forEach(function(item) {';

        foreach ($attributes as $attributeName) {
            $query .= '
                if (
                    typeof item.attributes.' . $attributeName . ' != undefined
                    && item.attributes.' . $attributeName . '.type == "' . self::MEDIA_CHOICE_TYPE . '"
                ) {
                    mediaId = "' . self::MEDIA_PREFIX . '" + item.attributes.' . $attributeName . '.value.id;

                    item.attributes.' . $attributeName . '.value = mediaId;
                    item.attributes.' . $attributeName . '.stringValue = mediaId;
                }';
        }

        $query .= '
                db.content.update({_id: item._id}, item);
            });';

        return $query;
    }

    /**
     * Get the remove the alternatives attribute
     *
     * @return string
     */
    protected function getRevertAlternativesRequest()
    {
        $filters = '
            {
                "mediaType": "' . self::MEDIA_TYPE_IMAGE . '",
                "thumbnail": /^' . self::THUMBNAIL_IMAGE_PREFIX . '/
            }';

        return '
            db.media.find(' . $filters . ').forEach(function(item) {
                delete item.alternatives;
                db.media.update({_id: item._id}, item);
            });';
    }

    /**
     * Get the request to revert the thumbnail attribute of audio media
     *
     * @return string
     */
    protected function getRevertAudioThumbnailRequest()
    {
        $filters = '
            {
                "mediaType": "' . self::MEDIA_TYPE_AUDIO . '"
            }';

        return '
            db.media.find(' . $filters . ').forEach(function(item) {
                delete item.thumbnail;
                db.media.update({_id: item._id}, item);
            });';
    }

    /**
     * Get the request to revert the thumbnail attribute of image media
     *
     * @return string
     */
    protected function getRevertImageThumbnailRequest()
    {
        $filters = '
            {
                "mediaType": "' . self::MEDIA_TYPE_IMAGE . '",
                "thumbnail": /^' . self::THUMBNAIL_IMAGE_PREFIX . '/
            }';

        return '
            db.media.find(' . $filters . ').forEach(function(item) {
                item.thumbnail = item.thumbnail.substring(16);
                db.media.update({_id: item._id}, item);
            });';
    }

    /**
     * Get the request to remove the mediaType attribute
     *
     * @return string
     */
    protected function getRevertMediaTypeRequest()
    {
        return '
            db.media.find().forEach(function(item) {
                delete item.mediaType;
                db.media.update({_id: item._id}, item);
            });';
    }
}
