<?php

namespace OpenOrchestra\MediaAdmin\Tests\Validator\Constraints;

use OpenOrchestra\Backoffice\Validator\Constraints\BooleanConditionValidator;
use OpenOrchestra\BaseBundle\Tests\AbstractTest\AbstractBaseTestCase;
use OpenOrchestra\MediaAdmin\Validator\Constraints\MediaTypeValidator;
use Phake;

/**
 * Class MediaTypeValidatorTest
 */
class MediaTypeValidatorTest extends AbstractBaseTestCase
{
    /**
     * @var BooleanConditionValidator
     */
    protected $validator;
    protected $context;
    protected $constraint;
    protected $constraintViolationBuilder;
    protected $mediaRepository;


    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->constraint = Phake::mock('Symfony\Component\Validator\Constraint');
        $this->context = Phake::mock('Symfony\Component\Validator\Context\ExecutionContextInterface');
        $this->constraintViolationBuilder = Phake::mock('Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface');
        $this->mediaRepository = Phake::mock('OpenOrchestra\Media\Repository\MediaRepositoryInterface');

        Phake::when($this->context)->buildViolation(Phake::anyParameters())->thenReturn($this->constraintViolationBuilder);

        $this->validator = new MediaTypeValidator($this->mediaRepository);
        $this->validator->initialize($this->context);
    }

    /**
     * Test instance
     */
    public function testClass()
    {
        $this->assertInstanceOf('Symfony\Component\Validator\ConstraintValidator', $this->validator);
    }

    /**
     * @param boolean $isType
     * @param int     $violationTimes
     *
     * @dataProvider provideFilterAndViolation
     */
    public function testValidate($isType, $violationTimes)
    {
        $mediaId = 'fakeId';
        Phake::when($this->mediaRepository)->isMediaTypeOf(Phake::anyParameters())->thenReturn($isType);
        $this->validator->validate($mediaId, $this->constraint);

        Phake::verify($this->context, Phake::times($violationTimes))->buildViolation(Phake::anyParameters());
    }

    /**
     * @return array
     */
    public function provideFilterAndViolation()
    {
        return array(
            array(true, 0),
            array(false, 1),
        );
    }
}
