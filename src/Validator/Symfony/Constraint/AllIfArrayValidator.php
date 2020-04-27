<?php

declare(strict_types=1);

namespace Mitra\Validator\Symfony\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\AllValidator as SymfonyAllValidator;

final class AllIfArrayValidator extends SymfonyAllValidator
{

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value)) {
            return;
        }

        parent::validate($value, $constraint);
    }
}
