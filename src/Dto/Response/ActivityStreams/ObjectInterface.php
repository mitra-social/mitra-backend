<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

interface ObjectInterface
{
    /**
     * @param string|array<mixed>|null $context
     * @return void
     */
    public function setContext($context): void;
}
