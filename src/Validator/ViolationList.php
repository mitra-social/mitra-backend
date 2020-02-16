<?php

declare(strict_types=1);

namespace Mitra\Validator;

use Respect\Validation\Exceptions\ValidationException;

final class ViolationList implements ViolationListInterface
{

    /**
     * @var array[]
     */
    private $violations;

    /**
     * @param array $violations
     */
    public function __construct(array $violations)
    {
        $this->violations = $violations;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->violations);
    }

    public function hasViolations(): bool
    {
        return 0 < $this->count();
    }
}
