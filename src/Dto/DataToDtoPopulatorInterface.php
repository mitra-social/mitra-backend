<?php

declare(strict_types=1);

namespace Mitra\Dto;

interface DataToDtoPopulatorInterface
{
    /**
     * @param array<mixed> $data
     * @return object
     * @throws DataToDtoPopulatorException
     */
    public function populate(array $data): object;
}
