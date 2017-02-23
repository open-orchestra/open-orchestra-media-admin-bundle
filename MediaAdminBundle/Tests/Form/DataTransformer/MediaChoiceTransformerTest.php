<?php

namespace OpenOrchestra\MediaAdminBundle\Tests\Form\DataTransformer;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdminBundle\Form\DataTransformer\MediaChoiceTransformer;

/**
 * Class MediaChoiceTransformerTest
 */
class MediaChoiceTransformerTest extends AbstractBaseTestCase
{
    /**
     * @var MediaChoiceTransformer
     */
    protected $transformer;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->transformer = new MediaChoiceTransformer();
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Form\DataTransformerInterface', $this->transformer);
    }

    /**
     * @param string $value
     * @param string $expected
     *
     * @dataProvider provideTransformData
     */
    public function testTransform($value, $expected)
    {
        $this->assertSame($expected, $this->transformer->transform($value));
    }

    /**
     * @return array
     */
    public function provideTransformData()
    {
        return array(
            'empty' => array('', array('id' => '', 'format' => '', 'alt' => '', 'legend' => '')),
            'id' => array(array('id' => 'id'), array('id' => 'id')),
        );
    }

    /**
     * @param string $value
     * @param string $expected
     *
     * @dataProvider provideReverseTransformData
     */
    public function testReverseTransform($value, $expected)
    {
        $this->assertSame($expected, $this->transformer->reverseTransform($value));
    }

    /**
     * @return array
     */
    public function provideReverseTransformData()
    {
        return array(
            'empty' => array('', array('id' => '', 'format' => '', 'alt' => '', 'legend' => '')),
            'id' => array(array('id' => 'id'), array('id' => 'id')),
        );
    }
}
