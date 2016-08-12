<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\ExtractReference\Strategies;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies\ExtractReferenceFromNodeStrategy;

/**
 * Class ExtractReferenceFromNodeStrategyTest
 */
class ExtractReferenceFromNodeStrategyTest extends AbstractBaseTestCase
{
    /**
     * @var ExtractReferenceFromNodeStrategy
     */
    protected $strategy;
    protected $parserBBcode;
    protected $tagManager;
    protected $nodeRepository;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->parserBBcode = Phake::mock('OpenOrchestra\BBcodeBundle\Parser\BBcodeParserInterface');
        Phake::when($this->parserBBcode)->parse(Phake::anyParameters())->thenReturn($this->parserBBcode);

        $this->tagManager = Phake::mock('OpenOrchestra\BaseBundle\Manager\TagManager');
        $this->nodeRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\NodeRepositoryInterface');

        $this->strategy = new ExtractReferenceFromNodeStrategy($this->parserBBcode, $this->tagManager, $this->nodeRepository);
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
     * @param string $reference
     * @param bool   $support
     *
     * @dataProvider provideReferenceAndSupport
     */
    public function testSupportReference($reference, $support)
    {
        $this->assertSame($support, $this->strategy->supportReference($reference));
    }

    /**
     * @return array
     */
    public function provideReferenceAndSupport()
    {
        return array(
            array('content-5646847541564561', false),
            array('node-5646847541564561', true),
            array('node-content-5646847541564561', true),
            array('', false),
            array('content-nodezùzdeldze', false),
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
        $block4 = Phake::mock('OpenOrchestra\ModelInterface\Model\BlockInterface');
        $blocks = new ArrayCollection();
        $blocks->add($block1);
        $blocks->add($block2);
        $blocks->add($block3);
        $blocks->add($block4);

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

        $mediaId = 'faleIdMedia';
        $elementNode = Phake::mock('OpenOrchestra\BBcodeBundle\ElementNode\BBcodeElementNode');
        Phake::when($elementNode)->getAsText()->thenReturn($mediaId);
        Phake::when($this->parserBBcode)->getElementByTagName(Phake::anyParameters())->thenReturn(array($elementNode));
        Phake::when($block4)->getAttributes()->thenReturn(array(
            'id' => 'id',
            'class' => 'class',
            'htmlContent' => "[media]".$mediaId."[/media]",
        ));

        $expected = array(
            'foo' => array('node-' . $nodeId . '-0', 'node-' . $nodeId . '-1'),
            'bar' => array('node-' . $nodeId . '-1', 'node-' . $nodeId . '-2'),
            'foo_col' => array('node-' . $nodeId . '-2'),
            'bar_col' => array('node-' . $nodeId . '-2'),
            'faleIdMedia' => array('node-' . $nodeId . '-3'),
        );

        $this->assertSame($expected, $this->strategy->extractReference($node));
    }

    /**
     * @param string $reference
     * @param mixed  $node
     * @param int    $countTagManager
     * @param string $expectedId
     *
     * @dataProvider provideReferenceAndNode
     */
    public function testInvalidateTagStatusableElement($reference, $node, $countTagManager, $expectedId)
    {
        Phake::when($this->nodeRepository)->findVersionByDocumentId(Phake::anyParameters())->thenReturn($node);

        $this->strategy->getInvalidateTagStatusableElement($reference);

        Phake::verify($this->tagManager, Phake::times($countTagManager))->formatNodeIdTag($expectedId);
    }

    /**
     * @return array
     */
    public function provideReferenceAndNode()
    {
        $node = Phake::mock('OpenOrchestra\ModelInterface\Model\NodeInterface');
        $nodeFakeId = 'fakeId';
        Phake::when($node)->getNodeId()->thenReturn($nodeFakeId);

        return array(
            array('content-54546465464', $node, 1, $nodeFakeId),
            array('content-nonContent', null, 0, null)
        );
    }

    /**
     * test name
     */
    public function testName()
    {
        $this->assertSame('node', $this->strategy->getName());
    }
}
