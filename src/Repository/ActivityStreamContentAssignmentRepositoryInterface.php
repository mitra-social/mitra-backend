<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\Actor\Actor;
use Mitra\Filtering\Filter;

interface ActivityStreamContentAssignmentRepositoryInterface
{
    /**
     * @param Actor $actor
     * @param Filter|null $filter
     * @param int $offset
     * @param int $limit
     * @return array<ActivityStreamContentAssignment>
     */
    public function findContentForActor(Actor $actor, ?Filter $filter, ?int $offset, ?int $limit): array;

    public function findAssignment(Actor $actor, ActivityStreamContent $content): ?ActivityStreamContentAssignment;

    public function getTotalCountForActor(Actor $actor, ?Filter $filter): int;
}
