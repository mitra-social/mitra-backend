<?php

declare(strict_types=1);

namespace Mitra\Validator\Symfony\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlankValidator as SymfonyNotBlankValidator;

final class NotBlankValidator extends SymfonyNotBlankValidator
{

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || false === $value || [] === $value) {
            return;
        }

        parent::validate($value, $constraint);
    }
}
