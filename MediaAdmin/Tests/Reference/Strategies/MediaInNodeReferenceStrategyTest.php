<?php

namespace OpenOrchestra\MediaAdmin\Tests\Reference\Strategies;

use Phake;
use OpenOrchestra\ModelInterface\Model\NodeInterface;
use OpenOrchestra\MediaAdmin\Reference\Strategies\MediaInNodeReferenceStrategy;

/**
 * Class MediaInNodeReferenceStrategyTest
 */
class MediaInNodeReferenceStrategyTest extends AbstractMediaReferenceStrategyTest
{
    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->strategy = new MediaInNodeReferenceStrategy($this->mediaRepository, $this->bbcodeParser);
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
            'Node'         => array($node, true),
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
        parent::checkAddReferencesToEntity($entity, $medias, NodeInterface::ENTITY_TYPE);
    }

    /**
     * @param mixed $entity
     * @param array $medias
     *
     * @dataProvider provideEntityWithMedias
     */
    public function testRemoveReferencesToEntity($entity, array $medias)
    {
        parent::checkRemoveReferencesToEntity($entity, $medias, NodeInterface::ENTITY_TYPE);
    }

    /**
     * @return array
     */
    public function provideEntityWithMedias()
    {
        $node = $this->createPhakeNode();
        $contentType = $this->createPhakeContentType();
        $content = $this->createPhakeContent();

        $mediaId = 'mediaId';
        $media = $this->createPhakeMedia($mediaId);
        $mediaBBCode = $this->createPhakeMedia($this->mediaInBBCodeId);

        $mediaBlock = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $attributeMedia = array('id' => $mediaId, 'format' => '');
        Phake::when($mediaBlock)->getAttributes()->thenReturn(array($attributeMedia));

        $TinyMCEblockWithoutMedia = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $attributeBBcodeNoMedia = $this->bbCodeWithNoMedia;
        Phake::when($TinyMCEblockWithoutMedia)->getAttributes()->thenReturn(array($attributeBBcodeNoMedia));

        $TinyMCEblockWithMedia = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $attributeBBcodeMedia = $this->bbCodeWithMedia;
        Phake::when($TinyMCEblockWithMedia)->getAttributes()->thenReturn(array($attributeBBcodeMedia));

        Phake::when($node)->getBlocks()->thenReturn(
            array($mediaBlock, $TinyMCEblockWithoutMedia, $TinyMCEblockWithMedia)
        );

        return array(
            'Media'        => array($media, array()),
            'Content'      => array($content, array()),
            'Content Type' => array($contentType, array()),
            'Node'         => array($node, array($mediaId => $media, $this->mediaInBBCodeId => $mediaBBCode))
        );
    }
}
