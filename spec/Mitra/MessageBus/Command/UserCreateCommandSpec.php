<?php

namespace spec\Mitra\MessageBus\Command;

use Mitra\MessageBus\Command\UserCreateCommand;
use Mitra\Entity\User\InternalUser;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

final class UserCreateCommandSpec extends ObjectBehavior
{

    public function let(): void
    {
        $user = new InternalUser(Uuid::uuid4()->toString(), 'foobar', 'foo@bar.com');
        $this->beConstructedWith($user);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UserCreateCommand::class);
    }
}
