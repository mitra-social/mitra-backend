<?php

namespace spec\Mitra\Dto\Populator;

use Mitra\Dto\Populator\ActivityPubDtoPopulator;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\MentionDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use PhpSpec\ObjectBehavior;

final class ActivityPubDtoPopulatorSpec extends ObjectBehavior
{
    public function it_populates_dto_successful(): void
    {
        $input = [
            'type' => 'Object',
            'id' => 'http://example.com/post/123456',
            'tag' => [
                [
                    'type' => 'Mention',
                    'href' => 'https://mastodon.social/users/fraenki',
                    'name' => '@fraenki',
                ]
            ],
        ];

        $result = $this->populate($input);

        $result->shouldBeAnInstanceOf(ObjectDto::class);
        /** @var ObjectDto $result */
        $result->type->shouldBe($input['type']);
        $result->id->shouldBe($input['id']);
        $result->tag->shouldBeArray();
        $result->tag[0]->shouldBeAnInstanceOf(MentionDto::class);
        $result->tag[0]->href->shouldBe($input['tag'][0]['href']);
        $result->tag[0]->name->shouldBe($input['tag'][0]['name']);
    }
}
