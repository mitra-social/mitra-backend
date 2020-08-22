<?php

declare(strict_types=1);

namespace Mitra\MessageBus\Handler\Command\ActivityPub;

use Mitra\MessageBus\Command\ActivityPub\AssignActorCommand;
use Mitra\Entity\User\InternalUser;
use Mitra\Slim\UriGeneratorInterface;
use Webmozart\Assert\Assert;

final class AssignActorCommandHandler
{
    /**
     * @var UriGeneratorInterface
     */
    private $uriGenerator;

    public function __construct(UriGeneratorInterface $uriGenerator)
    {
        $this->uriGenerator = $uriGenerator;
    }

    public function __invoke(AssignActorCommand $command): void
    {
        $actorUser = $command->getActor()->getUser();

        Assert::isInstanceOf($actorUser, InternalUser::class);

        /** @var InternalUser $actorUser */

        $command->getActivity()->actor = $this->uriGenerator->fullUrlFor('user-read', [
            'username' => $actorUser->getUsername()
        ]);
    }
}
