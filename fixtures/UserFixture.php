<?php

declare(strict_types=1);

namespace Mitra\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Mitra\Entity\User\InternalUser;
use Mitra\Entity\Actor\Person;

final class UserFixture extends AbstractFixture
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $user = new InternalUser('362408a8-67ac-4546-80f9-cb8a22364bfa', 'john.doe', 'john.doe@example.org');
        $user->setHashedPassword('$2y$10$DdhRHcSM1WpU.0QfgNqvc.TPL71CToS/0l/WQcQC7FfQliXtu09z.'); // helloworld
        $user->setCreatedAt(new \DateTime());

        $actor = new Person('31494782-a0b3-4327-b555-44d59b521d1f', $user);
        $actor->setName('John Doe');

        $manager->persist($user);
        $manager->persist($actor);
        $manager->flush();

        $this->addReference('user-john.doe', $user);
        $this->addReference('actor-john.doe', $actor);
    }
}
