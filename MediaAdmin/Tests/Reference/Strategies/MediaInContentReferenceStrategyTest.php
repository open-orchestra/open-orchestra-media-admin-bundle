<?php

namespace OpenOrchestra\MediaAdmin\Tests\Reference\Strategies;

use OpenOrchestra\ModelInterface\Model\ContentInterface;
use OpenOrchestra\MediaAdmin\Reference\Strategies\MediaInContentReferenceStrategy;

/**
 * Class MediaInContentReferenceStrategyTest
 */
class MediaInContentReferenceStrategyTest extends AbstractMediaReferenceStrategyTest
{
    /**
     * setUp
     */
    public function setUp()
    {
        parent::setup();

        $this->strategy = new MediaInContentReferenceStrategy($this->mediaRepository, $this->bbcodeParser);
    }

    /**
     * provide entity
     *
     * @return array
     */
    public function provideEntity()
    {
        $content = $this->createPhakeContent();
        $node = $this->createPhakeNode();
        $contentType = $this->createPhakeContentType();
        $media = $this->createPhakeMedia();

        return array(
            'Media'        => array($media, false),
            'Content'      => array($content, true),
            'Node'         => array($node, false),
            'Content Type' => array($contentType, false)
        );
    }

    /**
     * @param mixed  $entity
     * @param string $entityId
     * @param array  $medias
     *
     * @dataProvider provideEntityWithMedias
     */
    public function testAddReferencesToEntity($entity, $entityId, array $medias)
    {
        parent::checkAddReferencesToEntity($entity, $entityId, $medias, ContentInterface::ENTITY_TYPE, $this->mediaRepository);
    }

    /**
     * @param mixed  $entity
     * @param string $entityId
     * @param array  $medias
     *
     * @dataProvider provideEntityWithMedias
     */
    public function testRemoveReferencesToEntity($entity, $entityId, array $medias)
    {
        parent::checkRemoveReferencesToEntity($entity, $entityId, $medias, ContentInterface::ENTITY_TYPE, $this->mediaRepository);
    }

    /**
     * @return array
     */
    public function provideEntityWithMedias()
    {
        $nodeId = 'nodeId';
        $node = $this->createPhakeNode($nodeId);
        $contentTypeId = 'contentTypeId';
        $contentType = $this->createPhakeContentType($contentTypeId);

        $mediaId = 'mediaId';
        $media = $this->createPhakeMedia($mediaId);
        $mediaBBCode = $this->createPhakeMedia($this->mediaInBBCodeId);

        $attributeMedia = $this->createPhakeContentAttribute(array('id' => $mediaId, 'format' => ''));
        $attributeBBcodeNoMedia = $this->createPhakeContentAttribute($this->bbCodeWithNoMedia);
        $attributeBBcodeMedia = $this->createPhakeContentAttribute($this->bbCodeWithMedia);

        $contentId = 'contentId';
        $content = $this->createPhakeContent(
            $contentId,
            array($attributeMedia, $attributeBBcodeNoMedia, $attributeBBcodeMedia)
        );

        return array(
            'Media'        => array($media, $mediaId, array()),
            'Node'         => array($node, $nodeId, array()),
            'Content Type' => array($contentType, $contentTypeId, array()),
            'Content'      => array($content, $contentId, array($mediaId => $media, $this->mediaInBBCodeId => $mediaBBCode))
        );
    }
}
