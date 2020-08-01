<?php

declare(strict_types=1);

namespace Mitra\Repository;

use Mitra\Entity\Actor\Actor;
use Mitra\Entity\Subscription;

interface SubscriptionRepositoryInterface
{
    public function getByActors(Actor $subscribingActor, Actor $subscribedActor): ?Subscription;

    public function getFollowingCountForActor(Actor $actor): int;

    public function getFollowersOfActor(Actor $actor, ?int $offset, ?int $limit): array;

    public function getFollowerCountForActor(Actor $actor): int;
}
