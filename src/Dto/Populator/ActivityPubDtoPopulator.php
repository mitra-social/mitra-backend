<?php

declare(strict_types=1);

namespace Mitra\Dto\Populator;

use Mitra\Dto\DataToDtoPopulatorInterface;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Mapping\Dto\ActivityStreamTypeToDtoClassMapping;

final class ActivityPubDtoPopulator implements DataToDtoPopulatorInterface
{

    /**
     * @param mixed $data
     * @return mixed
     */
    private function resolveCoreTypeDto($data)
    {
        if (!is_array($data) || !array_key_exists('type', $data)) {
            return $data;
        }

        try {
            $typeDtoClass = ActivityStreamTypeToDtoClassMapping::map($data['type']);
        } catch (\RuntimeException $e) {
            $invalidDto = new ObjectDto();
            $invalidDto->type = $data['type'];

            return $invalidDto;
        }

        $dto = new $typeDtoClass();

        if (array_key_exists('@context', $data)) {
            $dto->context = $data['@context'];
            unset($data['@context']);
        }

        foreach ($data as $propertyName => $value) {
            if (!isset($data[$propertyName])) {
                $dto->$propertyName = null;
                continue;
            }

            $dto->$propertyName = $this->resolveCoreTypeDto($data[$propertyName]);
        }

        return $dto;
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data): object
    {
        $resolvedData = $this->resolveCoreTypeDto($data);

        return is_object($resolvedData) ? $resolvedData : new ObjectDto();
    }
}
