<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\ActivityStreamContent;
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
        $metadata->setPrimaryTable([
            'name' => 'activity_stream_content',
            'uniqueConstraints' => [
                'UNIQUE_EXTERNAL_CONTENT_ID' => ['columns' => ['external_id_hash', 'external_id']],
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
            'fieldName' => 'externalId',
            'columnName' => 'external_id',
            'type' => 'string',
            'length' => 255,
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'externalIdHash',
            'columnName' => 'external_id_hash',
            'type' => 'string',
            'length' => 64,
            'nullable' => false,
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

        // TODO currently the relation is the wrong way around. Switch column names later
        $metadata->mapManyToMany([
            'fieldName' => 'linkedObjects',
            'targetEntity' => ActivityStreamContent::class,
            'joinTable' => [
                'name' => 'activity_stream_content_linked_objects',
                'joinColumns' => [
                    [
                        'name' => 'linked_content_id',
                        'referencedColumnName' => 'id',
                    ],
                ],
                'inverseJoinColumns' => [
                    [
                        'name' => 'parent_content_id',
                        'referencedColumnName' => 'id',
                    ],
                ],
            ],
        ]);
    }
}
