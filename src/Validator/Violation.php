<?php

declare(strict_types=1);

namespace Mitra\Validator;

use Symfony\Component\Validator\ConstraintViolation;

final class Violation extends ConstraintViolation implements ViolationInterface
{
}
