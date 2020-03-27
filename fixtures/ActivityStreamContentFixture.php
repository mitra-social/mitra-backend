<?php

declare(strict_types=1);

namespace Mitra\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Mitra\Entity\ActivityStreamContent;

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
            'as-article' => new ActivityStreamContent(
                'd0d9d981-6fbd-4683-b6d3-c7a2bee03751',
                'Article',
                json_decode(file_get_contents(__DIR__ . '/resources/activitystream-objects/article.json'), true),
                null,
                null,
                null,
                null
            ),
            'as-document' => new ActivityStreamContent(
                '76e3506e-c038-4854-8f1a-0fb9c0ce4a1e',
                'Document',
                json_decode(file_get_contents(__DIR__ . '/resources/activitystream-objects/document.json'), true),
                null,
                null,
                null,
                null
            ),
            'as-audio' => new ActivityStreamContent(
                'fb70f0ad-2a6c-4878-b675-6cf75d190d8f',
                'Audio',
                json_decode(file_get_contents(__DIR__ . '/resources/activitystream-objects/audio.json'), true),
                null,
                null,
                null,
                null
            ),
            'as-image' => new ActivityStreamContent(
                'b8f29515-212d-4181-beb8-5a1b772a49c2',
                'Image',
                json_decode(file_get_contents(__DIR__ . '/resources/activitystream-objects/image.json'), true),
                null,
                null,
                null,
                null
            ),
            'as-note' => new ActivityStreamContent(
                '86f5051e-7cc2-49b7-a507-55bc48399c81',
                'Note',
                json_decode(file_get_contents(__DIR__ . '/resources/activitystream-objects/note.json'), true),
                null,
                null,
                null,
                null
            ),
            'as-event' => new ActivityStreamContent(
                'b06aea66-794d-4c42-ae5c-18f39016428c',
                'Event',
                json_decode(file_get_contents(__DIR__ . '/resources/activitystream-objects/event.json'), true),
                null,
                null,
                null,
                null
            ),
            'as-video' => new ActivityStreamContent(
                'd71aace6-7c03-4594-9523-e2ec6521a348',
                'Video',
                json_decode(file_get_contents(__DIR__ . '/resources/activitystream-objects/video.json'), true),
                null,
                null,
                null,
                null
            ),
        ];
    }
}
