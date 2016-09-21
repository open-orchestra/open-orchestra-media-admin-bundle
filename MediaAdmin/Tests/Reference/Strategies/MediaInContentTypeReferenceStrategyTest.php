<?php

namespace OpenOrchestra\MediaAdmin\Tests\Reference\Strategies;

use OpenOrchestra\MediaAdmin\Reference\Strategies\MediaInContentTypeReferenceStrategy;
use OpenOrchestra\ModelInterface\Model\ContentTypeInterface;
use Phake;

/**
 * Class MediaInContentTypeReferenceStrategyTest
 */
class MediaInContentTypeReferenceStrategyTest extends AbstractMediaReferenceStrategyTest
{
    /**
     * setUp
     */
    public function setUp()
    {
        parent::setup();

        $this->strategy = new MediaInContentTypeReferenceStrategy($this->mediaRepository, $this->bbcodeParser);
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
            'Content'      => array($content, false),
            'Node'         => array($node, false),
            'Content Type' => array($contentType, true)
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
        parent::checkAddReferencesToEntity($entity, $entityId, $medias, ContentTypeInterface::ENTITY_TYPE);
    }

    /**
     * @param mixed  $entity
     * @apram string $entityId
     * @param array  $medias
     *
     * @dataProvider provideEntityWithMedias
     */
    public function testRemoveReferencesToEntity($entity, $entityId, array $medias)
    {
        parent::checkRemoveReferencesToEntity($entity, $entityId, $medias, ContentTypeInterface::ENTITY_TYPE);
    }
    

    /**
     * @return array
     */
    public function provideEntityWithMedias()
    {
        $nodeId = 'nodeId';
        $node = $this->createPhakeNode($nodeId);
        $contentId = 'contentId';
        $content = $this->createPhakeContent($contentId);

        $mediaId = 'mediaId';
        $media = $this->createPhakeMedia($mediaId);

        $contentTypeWithoutMediaId = 'contentTypeWithoutMediaId';
        $contentTypeWithoutMedia = $this->createPhakeContentType($contentTypeWithoutMediaId);
        $fieldWithoutMedia = Phake::mock('OpenOrchestra\ModelInterface\Model\FieldTypeInterface');
        Phake::when($fieldWithoutMedia)->getDefaultValue()->thenReturn(array('fakeKey' => 'fakeValue'));
        Phake::when($contentTypeWithoutMedia)->getFields()->thenReturn(array($fieldWithoutMedia));

        $contentTypeWithMediaId = 'contentTypeWithMediaId';
        $contentTypeWithMedia = $this->createPhakeContentType($contentTypeWithMediaId);
        $fieldWithMedia = Phake::mock('OpenOrchestra\ModelInterface\Model\FieldTypeInterface');
        Phake::when($fieldWithMedia)->getDefaultValue()->thenReturn(array('id' => $mediaId, 'format' => 'original'));
        Phake::when($contentTypeWithMedia)->getFields()->thenReturn(array($fieldWithoutMedia, $fieldWithMedia));

        return array(
            'Node'                        => array($node, $nodeId, array()),
            'Content'                     => array($content, $contentId, array()),
            'Content type with no media'  => array($contentTypeWithoutMedia, $contentTypeWithoutMediaId, array()),
            'Content type with one media' => array($contentTypeWithMedia, $contentTypeWithMediaId, array($mediaId => $media))
        );
    }
}
