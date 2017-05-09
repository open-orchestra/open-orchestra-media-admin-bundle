<?php

namespace OpenOrchestra\MediaAdmin\Tests\Validator\Constraints;

use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdmin\Validator\Constraints\MediaType;
use Symfony\Component\Validator\Constraint;

/**
 * Class BooleanConditionTest
 */
class MediaTypeTest extends AbstractBaseTestCase
{
    /**
     * @var MediaType
     */
    protected $constraint;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->constraint = new MediaType();
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\Constraint', $this->constraint);
    }

    /**
     * test target
     */
    public function testTarget()
    {
        $this->assertSame(Constraint::PROPERTY_CONSTRAINT, $this->constraint->getTargets());
    }

    /**
     * test message
     */
    public function testMessages()
    {
        $this->assertSame('open_orchestra_media_admin_validators.media_type', $this->constraint->message);
    }

    /**
     * Test validate by
     */
    public function testValidateBy()
    {
        $this->assertSame('media_type', $this->constraint->validatedBy());
    }

    /**
     * Test get required options
     */
    public function testGetRequiredOptions()
    {
        $this->assertArrayHasKey('filter', $this->constraint->getRequiredOptions());
    }
}
