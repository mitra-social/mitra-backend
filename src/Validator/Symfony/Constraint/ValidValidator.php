<?php

declare(strict_types=1);

namespace Mitra\Validator\Symfony\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ValidValidator extends ConstraintValidator
{

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     * @return void
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Valid) {
            throw new UnexpectedTypeException($constraint, Valid::class);
        }

        if (null === $value || (!is_object($value) && !is_iterable($value))) {
            return;
        }

        /** @var string $group */
        $group = $this->context->getGroup();

        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->validate($value, null, [$group]);
    }
}
