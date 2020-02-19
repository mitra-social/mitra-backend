<?php

declare(strict_types=1);

namespace Mitra\Validator;

/**
 * @extends \ArrayObject<int, ViolationInterface>
 */
final class ViolationList extends \ArrayObject implements ViolationListInterface
{

    /**
     * @param array<ViolationInterface> $violations
     */
    public function __construct(array $violations = [])
    {
        parent::__construct($violations);
    }

    /**
     * @return array<int, ViolationInterface>
     */
    public function getViolations(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @return bool
     */
    public function hasViolations(): bool
    {
        return 0 < $this->count();
    }
}
