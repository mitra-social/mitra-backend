<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm\Actor;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface as ClassMapMappingInterfaceAlias;
use Doctrine\ORM\Mapping\ClassMetadata;

final class PersonOrmMapping implements ClassMapMappingInterfaceAlias
{

    /**
     * @inheritDoc
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
    }
}
