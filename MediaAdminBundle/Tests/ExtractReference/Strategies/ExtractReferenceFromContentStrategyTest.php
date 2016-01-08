<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\ExtractReference\Strategies;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies\ExtractReferenceFromContentStrategy;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Test ExtractReferenceFromContentStrategyTest
 */
class ExtractReferenceFromContentStrategyTest extends AbstractBaseTestCase
{
    /**
     * @var ExtractReferenceFromContentStrategy
     */
    protected $strategy;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->strategy = new ExtractReferenceFromContentStrategy();
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceInterface', $this->strategy);
    }

    /**
     * test Name
     */
    public function testName()
    {
        $this->assertSame('content', $this->strategy->getName());
    }

    /**
     * @param string $class
     * @param bool   $support
     *
     * @dataProvider provideClassAndSupport
     */
    public function testSupport($class, $support)
    {
        $this->assertSame($support, $this->strategy->support(Phake::mock($class)));
    }

    /**
     * @return array
     */
    public function provideClassAndSupport()
    {
        return array(
            array('OpenOrchestra\ModelInterface\Model\NodeInterface', false),
            array('OpenOrchestra\ModelInterface\Model\ContentInterface', true),
            array('OpenOrchestra\ModelInterface\Model\StatusableInterface', false),
        );
    }

    /**
     * Test extract
     */
    public function testExtractReference()
    {
        $contentAttribute1 = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentAttributeInterface');
        $contentAttribute2 = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentAttributeInterface');
        $contentAttributes = new ArrayCollection();
        $contentAttributes->add($contentAttribute1);
        $contentAttributes->add($contentAttribute2);

        $contentId = 'contentId';
        $content = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentInterface');
        Phake::when($content)->getId()->thenReturn($contentId);
        Phake::when($content)->getAttributes()->thenReturn($contentAttributes);

        Phake::when($contentAttribute1)->getValue()->thenReturn(MediaInterface::MEDIA_PREFIX . 'foo');
        Phake::when($contentAttribute2)->getValue()->thenReturn('class2');

        $expected = array(
            'foo' => array('content-' . $contentId),
        );

        $this->assertSame($expected, $this->strategy->extractReference($content));
    }
}
