<?php

namespace OpenOrchestra\MediaAdmin\Validator\Constraints;

use OpenOrchestra\Media\Repository\MediaRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class MediaTypeValidator
 */
class MediaTypeValidator extends ConstraintValidator
{
    protected $mediaRepository;

    /**
     * @param MediaRepositoryInterface $mediaRepository
     */
    public function __construct(MediaRepositoryInterface $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {

        dump($value);
        dump($this->mediaRepository->isMediaTypeOf($value, $constraint->filter));
        dump($constraint);
        $this->context->buildViolation($constraint->message)
             ->addViolation();
    }
}
