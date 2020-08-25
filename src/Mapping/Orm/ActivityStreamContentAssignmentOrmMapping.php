<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;

final class ActivityStreamContentAssignmentOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable([
            'name' => 'activity_stream_content_assignment',
            'uniqueConstraints' => [
                'UNIQUE_ACTOR_CONTENT' => ['columns' => ['actor_id', 'content_id']],
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

        $metadata->mapManyToOne([
            'fieldName' => 'actor',
            'targetEntity' => Actor::class,
            'joinColumns' => [
                [
                    'name' => 'actor_id',
                    'referencedColumnName' => 'user_id',
                    'nullable' => false,
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName' => 'content',
            'targetEntity' => ActivityStreamContent::class,
            'joinColumns' => [
                [
                    'name' => 'content_id',
                    'referencedColumnName' => 'id',
                    'nullable' => false,
                    'onDelete' => 'CASCADE',
                ],
            ],
        ]);
    }
}
