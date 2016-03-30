<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\EventSubscriber;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
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

    protected $event;
    protected $nodeEvent;
    protected $media;
    protected $status;
    protected $mediaRepository;
    protected $objectManager;
    protected $statusableElement;
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

        $this->status = Phake::mock('OpenOrchestra\ModelInterface\Model\StatusInterface');
        $this->statusableElement = Phake::mock('OpenOrchestra\ModelInterface\Model\StatusableInterface');
        Phake::when($this->statusableElement)->getStatus()->thenReturn($this->status);
        $this->event = Phake::mock('OpenOrchestra\ModelInterface\Event\StatusableEvent');
        Phake::when($this->event)->getStatusableElement()->thenReturn($this->statusableElement);

        $this->node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($this->node)->getNodeType()->thenReturn(NodeInterface::TYPE_TRANSVERSE);
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
        $this->assertArrayHasKey(StatusEvents::STATUS_CHANGE, $this->subscriber->getSubscribedEvents());
    }

    /**
     * @param bool   $isPublished
     * @param string $methodToCall
     *
     * @dataProvider provideStatusAndMethodToCall
     */
    public function testUpdateMediaReference($isPublished, $methodToCall)
    {
        Phake::when($this->status)->isPublished()->thenReturn($isPublished);
        Phake::when($this->extractReferenceManager)->extractReference(Phake::anyParameters())
            ->thenReturn(array(
                'foo' => array('node-nodeId-0', 'node-nodeId-1'),
                'bar' => array('node-nodeId-1'),
        ));

        $this->subscriber->updateMediaReference($this->event);

        Phake::verify($this->extractReferenceManager)->extractReference($this->statusableElement);
        Phake::verify($this->media, Phake::times(2))->$methodToCall('node-nodeId-1');
        Phake::verify($this->media)->$methodToCall('node-nodeId-0');

        Phake::verify($this->objectManager)->flush();
    }

    /**
     * @return array
     */
    public function provideStatusAndMethodToCall()
    {
        return array(
            array(true, 'addUsageReference'),
            array(false, 'removeUsageReference'),
        );
    }

    /**
     * @dataProvider provideMedias
     */
    public function testUpdateMediaReferenceForTransverserNode($pattern, $medias, $expectedRemove)
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

        $this->subscriber->updateMediaReferenceForTransverserNode($this->nodeEvent);

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
