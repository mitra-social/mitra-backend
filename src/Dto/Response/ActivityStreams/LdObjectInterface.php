<?php

declare(strict_types=1);

namespace Mitra\Dto\Response\ActivityStreams;

interface LdObjectInterface
{
    /**
     * @return array<mixed>
     */
    public function getContext(): array;
}
