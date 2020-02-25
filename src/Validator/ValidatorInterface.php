<?php

declare(strict_types=1);

namespace Mitra\Validator;

interface ValidatorInterface
{
    /**
     * @param object $object
     * @param array<string>|null $groups
     * @return ViolationListInterface
     */
    public function validate(object $object, array $groups = null): ViolationListInterface;
}
