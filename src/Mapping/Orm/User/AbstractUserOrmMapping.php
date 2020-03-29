<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm\User;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\User\ExternalUser;
use Mitra\Entity\User\InternalUser;

final class AbstractUserOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(['name' => '`user`']);
        $metadata->setInheritanceType(ClassMetadata::INHERITANCE_TYPE_JOINED);

        $metadata->setDiscriminatorColumn([
            'name' => 'type',
            'type' => 'string',
            'length' => 12,
        ]);

        $metadata->setDiscriminatorMap([
            'user' => InternalUser::class,
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
    }
}
