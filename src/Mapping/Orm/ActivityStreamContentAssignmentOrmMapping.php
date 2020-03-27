<?php

declare(strict_types=1);

namespace Mitra\Mapping\Orm;

use Chubbyphp\DoctrineDbServiceProvider\Driver\ClassMapMappingInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\User;
use Mitra\Repository\ActivityStreamContentAssignmentRepository;

final class ActivityStreamContentAssignmentOrmMapping implements ClassMapMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(['name' => 'activity_stream_content_assignment']);
        $metadata->setCustomRepositoryClass(ActivityStreamContentAssignmentRepository::class);

        $metadata->mapField([
            'fieldName' => 'id',
            'type' => 'string',
            'length' => 36,
            'id' => true,
            'strategy' => 'none',
            'unique' => true,
        ]);

        $metadata->mapManyToOne([
            'fieldName' => 'user',
            'targetEntity' => User::class,
            'joinColumns' => [
                [
                    'name' => 'user_id',
                    'referencedColumnName' => 'id',
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
                ],
            ],
        ]);
    }
}
