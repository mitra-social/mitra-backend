<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

final class UserOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(['name' => 'user']);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'string',
            'length' => 36,
            'id' => true,
            'strategy' => 'none',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'preferredUsername',
            'column_name' => 'preferred_username',
            'type' => 'string',
            'length' => 255,
            'nullable' => false,
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'email',
            'type' => 'datetime',
            'nullable' => false,
            'unique' => true,
        ]);

        /*$metadata->mapField([
            'fieldName' => 'createdAt',
            'columnName' => 'created_at',
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'updatedAt',
            'columnName' => 'updated_at',
            'type' => 'datetime',
            'nullable' => true,
        ]);*/
    }
}
