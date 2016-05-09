<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\ModelInterface\ContentEvents;
use OpenOrchestra\ModelInterface\NodeEvents;
use OpenOrchestra\ModelInterface\TrashcanEvents;
use Phake;
use OpenOrchestra\MediaAdminBundle\EventSubscriber\UpdateMediaReferenceSubscriber;
use OpenOrchestra\ModelInterface\StatusEvents;
use OpenOrchestra\ModelInterface\Model\NodeInterface;

/**
 * Test UpdateMediaReferenceSubscriberTest
 */
class UpdateMediaReferenceSubscriberTest extends AbstractBaseTestCase
{
    /**
     * @var UpdateMediaReferenceSubscriber
     */
    protected $subscriber;

    protected $nodeEvent;
    protected $media;
    protected $mediaRepository;
    protected $objectManager;
    protected $extractReferenceManager;
    protected $media1;
    protected $media2;
    protected $media3;
    protected $pattern1 = 'node-xxxx';
    protected $pattern2 = 'content-xxxx';

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->extractReferenceManager =
            Phake::mock('OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceManager');

        $this->node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        $this->nodeEvent = Phake::mock('OpenOrchestra\ModelInterface\Event\NodeEvent');
        Phake::when($this->nodeEvent)->getNode()->thenReturn($this->node);

        $this->media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        $this->mediaRepository = Phake::mock('OpenOrchestra\Media\Repository\MediaRepositoryInterface');
        Phake::when($this->mediaRepository)->find(Phake::anyParameters())->thenReturn($this->media);

        $this->objectManager = Phake::mock('Doctrine\Common\Persistence\ObjectManager');

        $this->media1 = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media1)->getUsageReference->thenReturn(array($this->pattern1));

        $this->media2 = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media2)->getUsageReference->thenReturn(array($this->pattern1, $this->pattern2, $this->pattern2));

        $this->media3 = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($this->media3)->getUsageReference->thenReturn(array($this->pattern1));

        $this->subscriber = new UpdateMediaReferenceSubscriber(
            $this->extractReferenceManager,
            $this->mediaRepository,
            $this->objectManager
        );
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    /**
     * Test subscribed events
     */
    public function testSubscribedEvents()
    {
        $this->assertArrayHasKey(NodeEvents::NODE_UPDATE_BLOCK, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(NodeEvents::NODE_DELETE_BLOCK, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(NodeEvents::NODE_UPDATE_BLOCK_POSITION, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(ContentEvents::CONTENT_UPDATE, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(ContentEvents::CONTENT_CREATION, $this->subscriber->getSubscribedEvents());
        $this->assertArrayHasKey(TrashcanEvents::TRASHCAN_REMOVE_ENTITY, $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test update reference
     */
    public function testUpdateReference()
    {
        Phake::when($this->extractReferenceManager)->extractReference(Phake::anyParameters())
            ->thenReturn(array(
                'foo' => array('node-nodeId-0', 'node-nodeId-1'),
                'bar' => array('node-nodeId-1'),
        ));
        Phake::when($this->mediaRepository)->findByUsagePattern(Phake::anyParameters())
            ->thenReturn(array());
        $this->subscriber->updateMediaReferencesFromNode($this->nodeEvent);

        Phake::verify($this->extractReferenceManager)->extractReference(Phake::anyParameters());
        Phake::verify($this->media, Phake::times(2))->addUsageReference('node-nodeId-1');
        Phake::verify($this->media)->addUsageReference('node-nodeId-0');

        Phake::verify($this->objectManager)->flush();
    }

    /**
     * @param string $pattern
     * @param array  $medias
     * @param int    $expectedRemove
     *
     * @dataProvider provideMedias
     */
    public function testRemoveReference($pattern, array $medias, $expectedRemove)
    {
        $mediaCollection = array();
        foreach ($medias as $media) {
            $mediaCollection[] = $this->{$media};
        }

        Phake::when($this->extractReferenceManager)->getReferencePattern(Phake::anyParameters())->thenReturn($pattern);
        Phake::when($this->mediaRepository)->findByUsagePattern(Phake::anyParameters())
            ->thenReturn($mediaCollection);
        Phake::when($this->extractReferenceManager)->extractReference(Phake::anyParameters())
            ->thenReturn(array());

        $this->subscriber->updateMediaReferencesFromNode($this->nodeEvent);

        Phake::verify($this->objectManager, Phake::times($expectedRemove))->persist(Phake::anyParameters());

        Phake::verify($this->objectManager)->flush();
    }

    /**
     * @return array
     */
    public function provideMedias()
    {
        return array(
             'pattern1' => array($this->pattern1, array('media1', 'media2', 'media3'), 3),
             'pattern2' => array($this->pattern2, array('media2'), 2),
             'patternEmpty' => array('', array(), 0),
        );
    }
}
