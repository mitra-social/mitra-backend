<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm\User;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Mapping\Orm\TimestampableOrmMappingTrait;

final class InternalUserOrmMapping implements ClassMapMappingInterface
{
    use TimestampableOrmMappingTrait;

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'user_internal']);

        $metadata->mapField([
            'fieldName' => 'username',
            'type' => 'string',
            'length' => 255,
            'nullable' => false,
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'email',
            'type' => 'string',
            'nullable' => false,
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'hashedPassword',
            'columnName' => 'password',
            'type' => 'string',
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'privateKey',
            'columnName' => 'private_key',
            'type' => 'text',
            'nullable' => false,
        ]);

        $this->configureTimestampableMapping($metadata);
    }
}
