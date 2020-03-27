<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

final class ActivityStreamContentOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(['name' => 'activity_stream_content']);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'string',
            'length' => 36,
            'id' => true,
            'strategy' => 'none',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'type',
            'type' => 'string',
            'length' => 36,
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'published',
            'column_name' => 'published',
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'updated',
            'column_name' => 'updated',
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'startDate',
            'column_name' => 'start_date',
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'endDate',
            'column_name' => 'end_date',
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'object',
            'column_name' => 'object',
            'type' => 'json',
            'nullable' => false,
        ]);
    }
}
