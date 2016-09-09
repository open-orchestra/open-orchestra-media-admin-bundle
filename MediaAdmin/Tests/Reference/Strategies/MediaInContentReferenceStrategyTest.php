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
     * @param mixed $entity
     * @param array $medias
     *
     * @dataProvider provideEntityWithMedias
     */
    public function testAddReferencesToEntity($entity, array $medias)
    {
        parent::checkAddReferencesToEntity($entity, $medias, ContentInterface::ENTITY_TYPE);
    }

    /**
     * @param mixed $entity
     * @param array $medias
     *
     * @dataProvider provideEntityWithMedias
     */
    public function testRemoveReferencesToEntity($entity, array $medias)
    {
        parent::checkRemoveReferencesToEntity($entity, $medias, ContentInterface::ENTITY_TYPE);
    }

    /**
     * @return array
     */
    public function provideEntityWithMedias()
    {
        $node = $this->createPhakeNode();
        $contentType = $this->createPhakeContentType();

        $mediaId = 'mediaId';
        $media = $this->createPhakeMedia($mediaId);
        $mediaBBCode = $this->createPhakeMedia($this->mediaInBBCodeId);

        $attributeMedia = $this->createPhakeContentAttribute(array('id' => $mediaId, 'format' => ''));
        $attributeBBcodeNoMedia = $this->createPhakeContentAttribute($this->bbCodeWithNoMedia);
        $attributeBBcodeMedia = $this->createPhakeContentAttribute($this->bbCodeWithMedia);

        $content = $this->createPhakeContent(
            'contentId',
            array($attributeMedia, $attributeBBcodeNoMedia, $attributeBBcodeMedia)
        );

        return array(
            'Media'        => array($media, array()),
            'Node'         => array($node, array()),
            'Content Type' => array($contentType, array()),
            'Content'      => array($content, array($mediaId => $media, $this->mediaInBBCodeId => $mediaBBCode))
        );
    }
}
