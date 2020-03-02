<?php

declare(strict_types=1);

namespace Mitra\Dto\Response;

final class ViolationDto
{
    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $messageTemplate;

    /**
     * @var array<mixed>
     */
    public $parameters;

    /**
     * @var string
     */
    public $propertyPath;

    /**
     * @var mixed
     */
    public $invalidValue;

    /**
     * @var string
     */
    public $code;
}
