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
        if ($value !== null && false === $this->mediaRepository->isMediaTypeOf($value, $constraint->filter)) {
            $this->context->buildViolation($constraint->message, array("%type%" => $constraint->filter))
                ->addViolation();
        }
    }
}
