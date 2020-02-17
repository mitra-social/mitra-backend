<?php

declare(strict_types=1);

namespace Mitra\Dto;

final class DtoToDataPopulator
{
    /**
     * @param object $dto
     * @return array
     */
    public function populate(object $dto): array
    {
        $data = get_object_vars($dto);

        foreach ($data as $key => $value) {
            if (!is_object($value)) {
                continue;
            }

            $data[$key] = $this->populate($value);
        }

        return $data;
    }
}
