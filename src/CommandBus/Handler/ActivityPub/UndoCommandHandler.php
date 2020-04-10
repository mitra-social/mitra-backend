<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Doctrine\ORM\EntityManagerInterface;
use Mitra\CommandBus\Command\ActivityPub\UndoCommand;
use Mitra\Entity\User\InternalUser;
use Mitra\Repository\ExternalUserRepository;
use Webmozart\Assert\Assert;

final class UndoCommandHandler
{
    /**
     * @var ExternalUserRepository
     */
    private $externalUserRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ExternalUserRepository $externalUserRepository, EntityManagerInterface $entityManager)
    {
        $this->externalUserRepository = $externalUserRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UndoCommand $command): void
    {
        $commandActor = $command->getActor();
        $commandActorUser = $commandActor->getUser();

        Assert::isInstanceOf($commandActorUser, InternalUser::class);

        /** @var InternalUser $commandActorUser */

        $undo = $command->getUndoDto();

        // TODO remove database record from follow table if object is follow object
        /*if ($undo->object instanceof FollowDto) {
        }*/
    }
}
