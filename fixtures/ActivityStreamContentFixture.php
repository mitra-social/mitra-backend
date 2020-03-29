<?php

declare(strict_types=1);

namespace Mitra\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Mitra\Entity\ActivityStreamContent;
use Mitra\Entity\Actor\Person;
use Mitra\Entity\User\ExternalUser;
use Ramsey\Uuid\Uuid;

final class ActivityStreamContentFixture extends AbstractFixture
{

    private $actorMap = [];

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getData() as $referenceName => $data) {
            $manager->persist($data);
            $this->addReference($referenceName, $data);
        }

        foreach ($this->actorMap as $actor) {
            $manager->persist($actor);
        }

        $manager->flush();
    }

    private function getData(): array
    {
        return [
            'as-article' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/article.json'
            ),
            'as-document' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/document.json'
            ),
            'as-audio' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/audio.json'
            ),
            'as-image' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/image.json'
            ),
            'as-note' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/note.json'
            ),
            'as-event' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/event.json'
            ),
            'as-video' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/video.json'
            ),
            'mastodon-create' => $this->getActivityStreamContentFromFile(
                __DIR__ . '/resources/activitystream-objects/mastodon-create.json'
            ),
        ];
    }

    private function getActivityStreamContentFromFile(string $filePath): ActivityStreamContent
    {
        $content = json_decode(file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);

        $attributedTo = $content['attributedTo'] ?? null;

        $attributedToActor = null;

        if (is_string($attributedTo)) {
            $actorIdHash = hash('sha256', $attributedTo);

            if (!isset($this->actorMap[$actorIdHash])) {
                $user = new ExternalUser(
                    Uuid::uuid4()->toString(),
                    $attributedTo,
                    $actorIdHash,
                    null,
                    $attributedTo . '/inbox',
                    $attributedTo . '/outbox'
                );

                $this->actorMap[$actorIdHash] = new Person(Uuid::uuid4()->toString(), $user);
            }

            $attributedToActor = $this->actorMap[$actorIdHash];
        } else {
            $actorIdHash = hash('sha256', json_encode($attributedTo));

            if (!isset($this->actorMap[$actorIdHash])) {
                $user = new ExternalUser(
                    Uuid::uuid4()->toString(),
                    $attributedTo['id'] ?? $actorIdHash,
                    $actorIdHash,
                    $attributedTo['preferredUsername'] ?? null,
                    $attributedTo['inbox'] ?? 'http://nirvana.org/inbox',
                    $attributedTo['outbox'] ?? 'http://nowhere.org/outbox'
                );

                $person = new Person(Uuid::uuid4()->toString(), $user);
                $person->setName($attributedTo['name'] ?? null);

                $this->actorMap[$actorIdHash] = $person;
            }

            $attributedToActor = $this->actorMap[$actorIdHash];
        }

        return new ActivityStreamContent(
            Uuid::uuid4()->toString(),
            $content['type'],
            $content,
            $attributedToActor,
            $this->parseDate($content['published'] ?? null),
            $this->parseDate($content['updated'] ?? null)
        );
    }

    private function parseDate(?string $dateStr): ?\DateTime
    {
        if (null === $dateStr) {
            return null;
        }

        return new \DateTime($dateStr);
    }
}
