<?php

declare(strict_types=1);

namespace Mitra\Validator;

interface ViolationInterface
{
    /**
     * Returns the violation message.
     * @return string|object
     */
    public function getMessage();

    /**
     * Returns the raw violation message.
     * @return string
     */
    public function getMessageTemplate();

    /**
     * Returns the parameters to be inserted into the raw violation message.
     * @return array<mixed>
     */
    public function getParameters();

    /**
     * Returns the property path from the root element to the violation.
     * @return string
     */
    public function getPropertyPath();

    /**
     * Returns the value that caused the violation.
     * @return mixed
     */
    public function getInvalidValue();

    /**
     * Returns a machine-digestible error code for the violation.
     * @return string|null The error code
     */
    public function getCode();
}
