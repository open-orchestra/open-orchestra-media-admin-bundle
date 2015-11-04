<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Functional\EventSubscriber;

use OpenOrchestra\Media\Model\MediaInterface;
use OpenOrchestra\MediaModelBundle\Document\Media;
use OpenOrchestra\ModelBundle\Document\Block;
use OpenOrchestra\ModelBundle\Document\Node;
use OpenOrchestra\BackofficeBundle\Tests\Functional\Controller\AbstractControllerTest;
use OpenOrchestra\ModelBundle\Document\Status;
use OpenOrchestra\ModelInterface\Event\StatusableEvent;
use OpenOrchestra\ModelInterface\StatusEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class UpdateMediaReferenceSubscriberTest
 *
 * @group media
 */
class UpdateMediaReferenceSubscriberTest extends AbstractControllerTest
{
    const ATTRIBUTE_ID_SUFFIX = "Id";
    const METHOD_SUFFIX = "BlockConfiguration";
    const REFERENCE_PREFIX = "node-";
    /**
     * @var Node node
     */
    protected $node;

    /**
     * @var array medias
     */
    protected $medias;

    /**
     * @var Status pending
     */
    protected $pending;

    /**
     * @var Status published
     */
    protected $published;

    /**
     * @var EventDispatcher eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Set up the test
     */
    public function setUp()
    {
        parent::setUp();

        $nodeRepository = static::$kernel->getContainer()->get('open_orchestra_model.repository.node');
        $this->node = $nodeRepository->findInLastVersion('root', 'en', '2');
        $mediaRepository = static::$kernel->getContainer()->get('open_orchestra_media.repository.media');
        $this->medias = array(
            $mediaRepository->findOneByName("Image 03"),
            $mediaRepository->findOneByName("Image 04")
        );
        $this->eventDispatcher = static::$kernel->getContainer()->get('event_dispatcher');
        $statusRepository = static::$kernel->getContainer()->get('open_orchestra_model.repository.status');
        $this->pending = $statusRepository->findOneByName('pending');
        $this->published = $statusRepository->findOneByName('published');
    }

    /**
     * @param array  $blockType
     * @param int    $mediaIndex
     *
     * @dataProvider provideMediaBlocks
     */
    public function testAddMediaBlocks(array $blockType, $mediaIndex)
    {
        $this->node->setStatus($this->pending);
        /** @var Media $media */
        $media = $this->medias[$mediaIndex];
        $this->checkMediaReference($media, null);

        $block = $this->generateBlock($blockType, 'ET9reyt');
        $this->node->addBlock($block);
        $attributes = $block->getAttributes();
        $attributes['pictures'] = array(MediaInterface::MEDIA_PREFIX . $media->getId());
        $attributes['id'] = $blockType["component"] . self::ATTRIBUTE_ID_SUFFIX;
        $method = $blockType["component"] . self::METHOD_SUFFIX;
        $attributes = $this->$method($attributes);
        $block->setAttributes($attributes);

        $event = new StatusableEvent($this->node, $this->published);
        $this->eventDispatcher->dispatch(StatusEvents::STATUS_CHANGE, $event);

        $mediaBlockIndex = $this->node->getBlockIndex($block);
        $expectedReference = self::REFERENCE_PREFIX . $this->node->getId() . "-" .  $mediaBlockIndex;
        $this->checkMediaReference($media, $expectedReference);
    }

    /**
     * @return array
     */
    public function provideMediaBlocks()
    {
        return array(
            array(array("component" => "gallery"), 0),
            array(array("component" => "slideshow"), 1),
        );
    }

    /**
     * @param MediaInterface $media
     * @param array          $expectedReference
     */
    protected function checkMediaReference($media, $expectedReference)
    {
        $references = $media->getUsageReference();
        $reference = isset($references[0]) ? $references[0] : null;
        $this->assertEquals($reference, $expectedReference);
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function slideshowBlockConfiguration($attributes)
    {
        $attributes['height'] = 200;
        $attributes['width'] = 250;

        return $attributes;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function galleryBlockConfiguration($attributes)
    {
        $attributes['imageFormat'] = 'original';
        $attributes['thumbnailFormat'] = 'original';

        return $attributes;
    }

    /**
     * @param string $blockType
     * @param string $id
     *
     * @return Block
     */
    protected function generateBlock($blockType, $id)
    {
        $block = new Block();
        $area = $this->node->getAreas()[0];
        $block->setComponent($blockType);
        $block->addArea(array($area->getAreaId()));
        $block->setId($id);

        return $block;
    }
}
