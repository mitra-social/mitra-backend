<?php

namespace spec\Mitra\CommandBus\Command;

use Mitra\CommandBus\Command\CreateUserCommand;
use Mitra\Entity\User;
use PhpSpec\ObjectBehavior;

class CreateUserCommandSpec extends ObjectBehavior
{

    public function let(): void
    {
        $user = new User('foobar', 'foo@bar.com');
        $this->beConstructedWith($user);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateUserCommand::class);
    }
}
