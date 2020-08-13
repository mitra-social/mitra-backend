<?php

declare(strict_types=1);

namespace Mitra\Slim;

interface IdGeneratorInterface
{
    public function getId(): string;
}
