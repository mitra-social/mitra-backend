<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm\User;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;

final class AbstractUserOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => '`user`']);
        $metadata->setInheritanceType(ClassMetadata::INHERITANCE_TYPE_JOINED);

        $metadata->setDiscriminatorColumn([
            'name' => 'type',
            'type' => 'string',
            'length' => 12,
        ]);

        $metadata->setDiscriminatorMap([
            'internal' => InternalUser::class,
            'external' => ExternalUser::class,
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
            'fieldName' => 'publicKey',
            'columnName' => 'public_key',
            'type' => 'text',
            'nullable' => true,
        ]);

        $metadata->mapOneToOne([
            'fieldName' => 'actor',
            'targetEntity' => Actor::class,
            'mappedBy' => 'user',
            'cascade' => ['persist', 'remove'],
        ]);
    }
}
