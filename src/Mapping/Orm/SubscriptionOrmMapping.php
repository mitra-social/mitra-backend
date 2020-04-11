<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\Actor\Actor;

final class SubscriptionOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
        $metadata->setPrimaryTable(['name' => 'subscription']);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'string',
            'length' => 36,
            'id' => true,
            'strategy' => 'none',
            'unique' => true,
        ]);

        $metadata->mapField([
            'fieldName' => 'startDate',
            'columnName' => 'start_date',
            'type' => 'datetime',
            'nullable' => false,
        ]);

        $metadata->mapField([
            'fieldName' => 'endDate',
            'columnName' => 'end_date',
            'type' => 'datetime',
            'nullable' => true,
        ]);

        $metadata->mapManyToOne([
            'fieldName' => 'subscribingActor',
            'targetEntity' => Actor::class,
            'joinColumns' => [
                [
                    'name' => 'subscribing_actor_id',
                    'referencedColumnName' => 'user_id',
                    'nullable' => false,
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName' => 'subscribedActor',
            'targetEntity' => Actor::class,
            'inversedBy' => 'actor',
            'joinColumns' => [
                [
                    'name' => 'subscribed_actor_id',
                    'referencedColumnName' => 'user_id',
                    'nullable' => false,
                ],
            ],
        ]);
    }
}
