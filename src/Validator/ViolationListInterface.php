<?php

declare(strict_types=1);

namespace Mitra\Validator;

interface ViolationListInterface extends \Traversable, \Countable, \ArrayAccess
{
    /**
     * @return array|ViolationInterface[]
     */
    public function getViolations(): array;

    /**
     * @return bool
     */
    public function hasViolations(): bool;
}
