<?php

namespace Mitra\Validator;

interface ViolationListInterface extends \Countable
{
    public function getViolations(): array;

    public function hasViolations(): bool;
}
