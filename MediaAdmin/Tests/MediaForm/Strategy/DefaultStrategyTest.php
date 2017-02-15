<?php

namespace OpenOrchestra\MediaAdmin\Tests\MediaForm\Strategy;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdmin\MediaForm\Strategy\DefaultStrategy as MediaFormDefaultStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\ImageStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\VideoStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\AudioStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\PdfStrategy;
use OpenOrchestra\MediaAdmin\FileAlternatives\Strategy\DefaultStrategy;
use Phake;

/**
 * Class DefaultStrategyTest
 */
class DefaultStrategyTest extends AbstractBaseTestCase
{
    protected $strategy;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->strategy = new MediaFormDefaultStrategy();
    }

    /**
     * Test Instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('OpenOrchestra\MediaAdmin\MediaForm\MediaFormStrategyInterface', $this->strategy);
    }

    /**
     * test support
     * 
     * @param string $mediaType
     * @param bool   $expectedSupport
     * 
     * @dataProvider provideMimeTypes
     */
    public function testSupport($mediaType, $expectedSupport)
    {
        $media = Phake::mock('OpenOrchestra\Media\Model\MediaInterface');
        Phake::when($media)->getMediaType()->thenReturn($mediaType);
        $this->assertSame($expectedSupport, $this->strategy->support($media));
    }

    /**
     * Provide Media to check mime types
     */
    public function provideMimeTypes()
    {
        return array(
            array(ImageStrategy::MEDIA_TYPE  , true),
            array(VideoStrategy::MEDIA_TYPE  , true),
            array(AudioStrategy::MEDIA_TYPE  , true),
            array(PdfStrategy::MEDIA_TYPE    , true),
            array(DefaultStrategy::MEDIA_TYPE, true)
        );
    }

    /**
     * test getFormType
     */
    public function testGetFormType()
    {
        $this->assertSame('oo_media_base', $this->strategy->getFormType());
    }

    /**
     * test getName
     */
    public function testGetName()
    {
        $this->assertSame('default_media_form_strategy', $this->strategy->getName());
    }
}
