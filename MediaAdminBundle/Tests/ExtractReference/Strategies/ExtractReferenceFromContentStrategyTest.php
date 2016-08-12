<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\ExtractReference\Strategies;

use Doctrine\Common\Collections\ArrayCollection;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use Phake;
use OpenOrchestra\MediaAdminBundle\ExtractReference\Strategies\ExtractReferenceFromContentStrategy;

/**
 * Test ExtractReferenceFromContentStrategyTest
 */
class ExtractReferenceFromContentStrategyTest extends AbstractBaseTestCase
{
    /**
     * @var ExtractReferenceFromContentStrategy
     */
    protected $strategy;
    protected $parserBBcode;
    protected $contentRepository;
    protected $tagManager;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->parserBBcode = Phake::mock('OpenOrchestra\BBcodeBundle\Parser\BBcodeParserInterface');
        Phake::when($this->parserBBcode)->parse(Phake::anyParameters())->thenReturn($this->parserBBcode);

        $this->tagManager = Phake::mock('OpenOrchestra\BaseBundle\Manager\TagManager');
        $this->contentRepository = Phake::mock('OpenOrchestra\ModelInterface\Repository\ContentRepositoryInterface');

        $this->strategy = new ExtractReferenceFromContentStrategy($this->parserBBcode, $this->tagManager, $this->contentRepository);
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
            array('content-5646847541564561', true),
            array('node-5646847541564561', false),
            array('node-content-5646847541564561', false),
            array('', false),
            array('content-nodezùzdeldze', true),
        );
    }

    /**
     * Test extract
     */
    public function testExtractReference()
    {
        $contentAttribute1 = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentAttributeInterface');
        $contentAttribute2 = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentAttributeInterface');
        $contentAttribute3 = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentAttributeInterface');
        $contentAttributes = new ArrayCollection();
        $contentAttributes->add($contentAttribute1);
        $contentAttributes->add($contentAttribute2);
        $contentAttributes->add($contentAttribute3);

        $contentId = 'contentId';
        $content = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentInterface');
        Phake::when($content)->getId()->thenReturn($contentId);
        Phake::when($content)->getAttributes()->thenReturn($contentAttributes);

        Phake::when($contentAttribute1)->getValue()->thenReturn(array('id' => 'foo', 'format' => ''));
        Phake::when($contentAttribute2)->getValue()->thenReturn('class2');

        $mediaId = 'faleIdMedia';
        $elementNode = Phake::mock('OpenOrchestra\BBcodeBundle\ElementNode\BBcodeElementNode');
        Phake::when($elementNode)->getAsText()->thenReturn($mediaId);
        Phake::when($this->parserBBcode)->getElementByTagName(Phake::anyParameters())->thenReturn(array($elementNode));
        Phake::when($contentAttribute3)->getValue()->thenReturn('<p>teststes</p>[media={"format":"fixed_height"}]'.$mediaId.'[/media]');

        $expected = array(
            'foo' => array('content-' . $contentId),
            'faleIdMedia' => array('content-' . $contentId),
        );

        $this->assertSame($expected, $this->strategy->extractReference($content));
    }

    /**
     * @param string $reference
     * @param mixed  $content
     * @param int    $countTagManager
     * @param string $expectedId
     *
     * @dataProvider provideReferenceAndContent
     */
    public function testInvalidateTagStatusableElement($reference, $content, $countTagManager, $expectedId)
    {
        Phake::when($this->contentRepository)->findById(Phake::anyParameters())->thenReturn($content);

        $this->strategy->getInvalidateTagStatusableElement($reference);

        Phake::verify($this->tagManager, Phake::times($countTagManager))->formatContentIdTag($expectedId);
    }

    /**
     * @return array
     */
    public function provideReferenceAndContent()
    {
        $content1 = Phake::mock('OpenOrchestra\ModelInterface\Model\ContentInterface');
        $content1FakeId = 'fakeId';
        Phake::when($content1)->getContentId()->thenReturn($content1FakeId);

        return array(
          array('content-54546465464', $content1, 1, $content1FakeId),
          array('content-nonContent', null, 0, null)
        );
    }
}
