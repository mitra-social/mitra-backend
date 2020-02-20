<?php

declare(strict_types=1);

namespace Mitra\Validator;

/**
 * @extends \ArrayAccess<int, ViolationInterface>
 * @extends \Traversable<ViolationInterface>
 */
interface ViolationListInterface extends \Traversable, \Countable, \ArrayAccess
{
    /**
     * @return array<int,ViolationInterface>
     */
    public function getViolations(): array;

    /**
     * @return bool
     */
    public function hasViolations(): bool;
}
