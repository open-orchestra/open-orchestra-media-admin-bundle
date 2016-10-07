<?php

namespace OpenOrchestra\MediaAdmin\Tests\Reference\Strategies;

use Phake;
use OpenOrchestra\MediaAdmin\Reference\Strategies\KeywordInMediaReferenceStrategy;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class KeywordInMediaReferenceStrategyTest
 */
class KeywordInMediaReferenceStrategyTest extends AbstractMediaReferenceStrategyTest
{
    protected $keywordRepository;

    /**
     * setUp
     */
    public function setUp()
    {
        $this->keywordRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\KeywordRepositoryInterface');
        $this->strategy = new KeywordInMediaReferenceStrategy($this->keywordRepository);
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
            'Media'        => array($media, true),
            'Content'      => array($content, false),
            'Node'         => array($node, false),
            'Content Type' => array($contentType, false)
        );
    }

    /**
     * @param mixed $entity
     * @param string $entityId
     * @param array $keywords
     *
     * @dataProvider provideEntityWithKeywords
     */
    public function testAddReferencesToEntity($entity, $entityId, array $keywords)
    {
        Phake::when($entity)->getKeywords()->thenReturn($keywords);

        foreach ($keywords as $keywordId => $keyword) {
            Phake::when($this->keywordRepository)->find($keywordId)->thenReturn($keyword);
        }

        $this->strategy->addReferencesToEntity($entity);

        foreach ($keywords as $keyword) {
            Phake::verify($keyword)->addUseInEntity($entityId, MediaInterface::ENTITY_TYPE);
        }
    }

    /**
     * @param mixed  $entity
     * @param string $entityId
     * @param array  $keywords
     *
     * @dataProvider provideEntityWithKeywords
     */
    public function testRemoveReferencesToEntity($entity, $entityId, array $keywords)
    {
        Phake::when($this->keywordRepository)->findByUsedInEntity(Phake::anyParameters())->thenReturn($keywords);

        $this->strategy->removeReferencesToEntity($entity);

        foreach ($keywords as $keyword) {
            Phake::verify($keyword)->removeUseInEntity($entityId, MediaInterface::ENTITY_TYPE);
        }
    }

    /**
     * @return array
     */
    public function provideEntityWithKeywords()
    {
        $nodeId= 'nodeId';
        $node = $this->createPhakeNode($nodeId);
        $contentId = 'contentId';
        $content = $this->createPhakeContent($contentId);
        $contentType = $this->createPhakeContentType($contentId);
        $mediaId = 'mediaId';
        $media = $this->createPhakeMedia($mediaId);

        $keyword1Id = 'keyword1';
        $keyword2Id = 'keyword2';
        $keyword3Id = 'keyword3';

        $keyword1 = $this->createPhakeKeyword($keyword1Id);
        $keyword2 = $this->createPhakeKeyword($keyword2Id);
        $keyword3 = $this->createPhakeKeyword($keyword3Id);

        return array(
            'Node'                    => array($node, $nodeId, array()),
            'Content'                 => array($content, $contentId, array()),
            'Content type'            => array($contentType, $contentId, array()),
            'Media with no keyword'   => array($media, $mediaId, array()),
            'Media with one keyword'  => array($media, $mediaId, array($keyword1Id => $keyword1)),
            'Media with two keywords' => array($media, $mediaId, array($keyword2Id => $keyword2, $keyword3Id => $keyword3))
        );
    }
}
