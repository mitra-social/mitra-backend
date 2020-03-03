<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Repository\UserRepository;

final class UserOrmMapping implements ClassMapMappingInterface
{
    use TimestampableOrmMappingTrait;

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(['name' => '`user`']);
        $metadata->setCustomRepositoryClass(UserRepository::class);

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

        $this->configureTimestampableMapping($metadata);
    }
}
