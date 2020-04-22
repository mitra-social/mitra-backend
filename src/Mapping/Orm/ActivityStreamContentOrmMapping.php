<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\User\AbstractUser;

final class ActivityStreamContentOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata): void
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
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'updated',
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'object',
            'type' => 'json',
            'nullable' => false,
        ]);

        $metadata->mapManyToOne([
            'fieldName' => 'attributedTo',
            'targetEntity' => Actor::class,
            'joinColumns' => [
                [
                    'name' => 'attributed_to',
                    'referencedColumnName' => 'user_id',
                    'nullable' => true,
                ],
            ],
        ]);
    }
}
