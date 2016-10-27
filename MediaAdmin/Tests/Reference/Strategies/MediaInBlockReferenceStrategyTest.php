<?php

namespace OpenOrchestra\MediaAdmin\Tests\Reference\Strategies;

use Phake;
use OpenOrchestra\ModelInterface\Model\BlockInterface;
use OpenOrchestra\MediaAdmin\Reference\Strategies\MediaInBlockReferenceStrategy;

/**
 * Class MediaInBlockReferenceStrategyTest
 */
class MediaInBlockReferenceStrategyTest extends AbstractMediaReferenceStrategyTest
{
    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->strategy = new MediaInBlockReferenceStrategy($this->mediaRepository, $this->bbcodeParser);
    }

    /**
     * provide entity
     *
     * @return array
     */
    public function provideEntity()
    {
        $content = $this->createPhakeContent();
        $block = $this->createPhakeBlock();
        $contentType = $this->createPhakeContentType();
        $media = $this->createPhakeMedia();

        return array(
            'Media'        => array($media, false),
            'Content'      => array($content, false),
            'Block'         => array($block, true),
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
        parent::checkAddReferencesToEntity($entity, $entityId, $medias, BlockInterface::ENTITY_TYPE, $this->mediaRepository);
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
        parent::checkRemoveReferencesToEntity($entity, $entityId, $medias, BlockInterface::ENTITY_TYPE, $this->mediaRepository);
    }

    /**
     * @return array
     */
    public function provideEntityWithMedias()
    {
        $contentTypeId = 'contentTypeId';
        $contentType = $this->createPhakeContentType($contentTypeId);
        $contentId = 'contentId';
        $content = $this->createPhakeContent($contentId);

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

        return array(
            'Media'        => array($media, $mediaId, array()),
            'Content'      => array($content, $contentId, array()),
            'Content Type' => array($contentType, $contentTypeId, array()),
        );
    }
}
