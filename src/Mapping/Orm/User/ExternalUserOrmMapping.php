<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm\User;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

final class ExternalUserOrmMapping implements ClassMapMappingInterface
{
    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'user_external']);

        $metadata->mapField([
            'fieldName' => 'externalId',
            'columnName' => 'external_id',
            'type' => 'string',
            'length' => 255,
            'strategy' => 'none',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'externalIdHash',
            'columnName' => 'external_id_hash',
            'type' => 'string',
            'length' => 64,
            'strategy' => 'none',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'preferredUsername',
            'columnName' => 'preferred_username',
            'type' => 'string',
            'length' => 255,
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'outbox',
            'type' => 'string',
            'length' => 2048,
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'inbox',
            'type' => 'string',
            'length' => 2048,
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'following',
            'type' => 'string',
            'length' => 2048,
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'followers',
            'type' => 'string',
            'length' => 2048,
            'nullable' => true,
        ]);
    }
}
