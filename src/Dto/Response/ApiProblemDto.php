<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto\Response;

final class ApiProblemDto
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    public $detail;

    /**
     * @var string|null
     */
    public $instance;
}
