<?php

declare(strict_types=1);

namespace Mitra\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\ActivityStreamContentAssignment;
use Mitra\Entity\User;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

final class ActivityStreamContentAssignmentFixture extends AbstractFixture implements DependentFixtureInterface
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $asContent = [
            'as-article',
            'as-document',
            'as-audio',
            'as-image',
            'as-note',
            'as-event',
            'as-video',
            'mastodon-create'
        ];

        foreach ($asContent as $referenceName) {
            $user = $this->getReference('user-john.doe');
            Assert::isInstanceOf($user, User::class);

            $content = $this->getReference($referenceName);
            Assert::isInstanceOf($content, ActivityStreamContent::class);

            $assignment = new ActivityStreamContentAssignment(Uuid::uuid4()->toString(), $user, $content);

            $manager->persist($assignment);
        }

        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return [
            UserFixture::class,
            ActivityStreamContentFixture::class,
        ];
    }
}
