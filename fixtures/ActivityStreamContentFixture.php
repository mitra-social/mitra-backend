<?php

declare(strict_types=1);

namespace Mitra\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Mitra\Entity\ActivityStreamContent;
use Ramsey\Uuid\Uuid;

final class ActivityStreamContentFixture extends AbstractFixture
{

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getData() as $referenceName => $data) {
            $manager->persist($data);
            $this->addReference($referenceName, $data);
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

        return new ActivityStreamContent(
            Uuid::uuid4()->toString(),
            $content['type'],
            $content,
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
