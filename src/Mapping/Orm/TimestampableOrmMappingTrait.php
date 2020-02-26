<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Doctrine\ORM\Mapping\ClassMetadata;

trait TimestampableOrmMappingTrait
{

    /**
     * @param ClassMetadata $metadata
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    private function configureTimestampableMapping(ClassMetadata $metadata):  void
    {
        $metadata->mapField([
            'fieldName' => 'createdAt',
            'columnName' => 'created_at',
            'type' => 'datetime',
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'updatedAt',
            'columnName' => 'updated_at',
            'type' => 'datetime',
            'nullable' => true,
        ]);
    }
}
