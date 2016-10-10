<?php

namespace OpenOrchestra\MediaAdmin\Tests\Reference\Strategies;

use OpenOrchestra\Backoffice\Tests\Reference\Strategies\AbstractReferenceStrategyTest;
use Phake;

/**
 * Class AbstractMediaReferenceStrategyTest
 */
abstract class AbstractMediaReferenceStrategyTest extends AbstractReferenceStrategyTest
{
    protected $mediaRepository;
    protected $bbcodeParser;

    protected $mediaInBBCodeId = 'mediaInBBCodeId';
    protected $bbCodeWithNoMedia;
    protected $bbCodeWithMedia;

    /**
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->bbCodeWithNoMedia = 'Some [b]String[b]';
        $this->bbCodeWithMedia = 'Some [b]String[b] with [media={"format":"fixed_height"}]' . $this->mediaInBBCodeId . '[/media]';
    }

    /**
     * setUp
     */
    public function setUp()
    {
        $this->mediaRepository = Phake::mock('OpenOrchestra\Media\Repository\MediaRepositoryInterface');

        $this->bbcodeParser = Phake::mock('OpenOrchestra\BBcodeBundle\Parser\BBcodeParser');
        Phake::when($this->bbcodeParser)->parse($this->bbCodeWithMedia)->thenReturn($this->bbcodeParser);
        Phake::when($this->bbcodeParser)->parse($this->bbCodeWithMedia)->thenReturn($this->bbcodeParser);
    }

    /**
     * @param mixed  $entity
     * @param string $entityId
     * @param array  $medias
     * @param string $entityType
     * @param mixed  $itemRepository
     */
    protected function checkAddReferencesToEntity($entity, $entityId, array $medias, $entityType, $itemRepository)
    {
        $mediaTag = Phake::mock('OpenOrchestra\BBcodeBundle\ElementNode\BBcodeElementNodeInterface');
        Phake::when($mediaTag)->getAsText()->thenReturn($this->mediaInBBCodeId);
        Phake::when($this->bbcodeParser)->getElementByTagName(Phake::anyParameters())->thenReturn(
            array($mediaTag)
        );

        parent::checkAddReferencesToEntity($entity, $entityId, $medias, $entityType, $itemRepository);
    }

    /**
     * @param mixed  $entity
     * @param string $entityId
     * @param array  $medias
     * @param string $entityType
     * @param mixed  $itemRepository
     */
    protected function checkRemoveReferencesToEntity($entity, $entityId, array $medias, $entityType, $itemRepository)
    {
        parent::checkRemoveReferencesToEntity($entity, $entityId, $medias, $entityType, $itemRepository);
    }

    /**
     * Create a Phake Media
     *
     * @param string $mediaId
     *
     * @return \Phake_IMock
     */
    protected function createPhakeMedia($mediaId = 'mediaId')
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getId()->thenReturn($mediaId);

        return $media;
    }
}
