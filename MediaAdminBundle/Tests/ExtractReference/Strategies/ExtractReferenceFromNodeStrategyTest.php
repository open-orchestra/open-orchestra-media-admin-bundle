<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\ExtractReference\Strategies;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies\ExtractReferenceFromNodeStrategy;
use OpenOrchestra\Media\Model\MediaInterface;

/**
 * Class ExtractReferenceFromNodeStrategyTest
 */
class ExtractReferenceFromNodeStrategyTest extends AbstractBaseTestCase
{
    /**
     * @var ExtractReferenceFromNodeStrategy
     */
    protected $strategy;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->strategy = new ExtractReferenceFromNodeStrategy();
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf(
            'OpenOrchestra\MediaAdminBundle\ExtractReference\ExtractReferenceInterface',
            $this->strategy
        );
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
            array('OpenOrchestra\ModelInterface\Model\NodeInterface', true),
            array('OpenOrchestra\ModelInterface\Model\ContentInterface', false),
            array('OpenOrchestra\ModelInterface\Model\StatusableInterface', false),
        );
    }

    /**
     * Test extract
     */
    public function testExtractReference()
    {
        $block1 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $block2 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $block3 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $blocks = new ArrayCollection();
        $blocks->add($block1);
        $blocks->add($block2);
        $blocks->add($block3);

        $nodeId = 'nodeId';
        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        Phake::when($node)->getId()->thenReturn($nodeId);
        Phake::when($node)->getBlocks()->thenReturn($blocks);

        Phake::when($block1)->getAttributes()->thenReturn(array(
            'id' => 'id',
            'class' => 'class',
            'media' => array('id' => 'foo', 'format' => ''),
        ));
        Phake::when($block2)->getAttributes()->thenReturn(array(
            'id' => 'id2',
            'class' => 'class2',
            'media1' => array('id' => 'foo', 'format' => ''),
            'media2' => array('id' => 'bar', 'format' => ''),
        ));
        Phake::when($block3)->getAttributes()->thenReturn(array(
            'id' => 'id3',
            'class' => 'class3',
            'mediaSingle' => array('id' => 'bar', 'format' => ''),
            'mediaCollection' => array(
                array('id' => 'foo_col', 'format' => ''),
                array('id' => 'bar_col', 'format' => '')
            )
        ));

        $expected = array(
            'foo' => array('node-' . $nodeId . '-0', 'node-' . $nodeId . '-1'),
            'bar' => array('node-' . $nodeId . '-1', 'node-' . $nodeId . '-2'),
            'foo_col' => array('node-' . $nodeId . '-2'),
            'bar_col' => array('node-' . $nodeId . '-2'),
        );

        $this->assertSame($expected, $this->strategy->extractReference($node));
    }

    /**
     * test name
     */
    public function testName()
    {
        $this->assertSame('node', $this->strategy->getName());
    }
}
