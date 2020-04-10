<?php

declare(strict_types=1);

namespace Mitra\CommandBus\Handler\ActivityPub;

use Mitra\CommandBus\Command\ActivityPub\AssignActorCommand;
use Mitra\Entity\User\InternalUser;
use Mitra\Slim\UriGenerator;
use Webmozart\Assert\Assert;

final class AssignActorCommandHandler
{
    /**
     * @var UriGenerator
     */
    private $uriGenerator;

    public function __construct(UriGenerator $uriGenerator)
    {
        $this->uriGenerator = $uriGenerator;
    }

    public function __invoke(AssignActorCommand $command)
    {
        $actorUser = $command->getActor()->getUser();

        Assert::isInstanceOf($actorUser, InternalUser::class);

        /** @var InternalUser $actorUser */

        $command->getActivity()->actor = $this->uriGenerator->fullUrlFor('user-read', [
            'preferredUsername' => $actorUser->getUsername()
        ]);
    }
}
