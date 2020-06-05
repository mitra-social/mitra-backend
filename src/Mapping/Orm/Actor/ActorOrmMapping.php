<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm\Actor;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\Media;
use Mitra\Entity\User\AbstractUser;
use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Actor\Organization;
use Mitra\Entity\Actor\Person;

final class ActorOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(['name' => 'actor']);
        $metadata->setInheritanceType(ClassMetadata::INHERITANCE_TYPE_SINGLE_TABLE);

        $metadata->setDiscriminatorColumn([
            'name' => 'type',
            'type' => 'string',
            'length' => 12,
        ]);

        $metadata->setDiscriminatorMap([
            'actor' => Actor::class,
            'person' => Person::class,
            'organization' => Organization::class,
        ]);

        $metadata->mapField([
            'fieldName' => 'name',
            'type' => 'string',
            'length' => 2048,
            'nullable' => true,
        ]);

        $metadata->mapOneToOne([
            'fieldName' => 'user',
            'id' => true,
            'targetEntity' => AbstractUser::class,
            'inversedBy' => 'actor',
            'cascade' => ['all'],
            'joinColumns' => [
                [
                    'name' => 'user_id',
                    'referencedColumnName' => 'id',
                    'nullable' => false,
                ]
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName' => 'icon',
            'targetEntity' => Media::class,
            'joinColumns' => [
                [
                    'name' => 'icon',
                    'referencedColumnName' => 'id',
                    'nullable' => true,
                ],
            ],
        ]);
    }
}
