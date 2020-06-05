<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

final class MediaOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable([
            'name' => 'media',
            'indexes' => [
                'INDEX_CHECKSUM' => ['columns' => ['checksum']],
                'INDEX_ORIGINAL_URI_HASH' => ['columns' => ['original_uri_hash']],
                'INDEX_LOCAL_URI_HASH' => ['columns' => ['local_uri_hash']],
            ],
        ]);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'string',
            'length' => 36,
            'id' => true,
            'strategy' => 'none',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'checksum',
            'type' => 'string',
            'length' => 64,
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'originalUri',
            'columnName' => 'original_uri',
            'type' => 'string',
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'originalUriHash',
            'columnName' => 'original_uri_hash',
            'type' => 'string',
            'length' => 64,
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'localUri',
            'type' => 'string',
            'nullable' => false,
        ]);
    }
}
