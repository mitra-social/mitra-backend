<?php

declare(strict_types=1);

namespace Mitra\Mapping\Dto;

use Mitra\Dto\Response\ActivityPub\Actor\PersonDto;
use Mitra\Dto\Response\ActivityStreams\Activity\CreateDto;
use Mitra\Dto\Response\ActivityStreams\Activity\FollowDto;
use Mitra\Dto\Response\ActivityStreams\Activity\UndoDto;
use Mitra\Dto\Response\ActivityStreams\ArticleDto;
use Mitra\Dto\Response\ActivityStreams\AudioDto;
use Mitra\Dto\Response\ActivityStreams\DocumentDto;
use Mitra\Dto\Response\ActivityStreams\EventDto;
use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\MentionDto;
use Mitra\Dto\Response\ActivityStreams\NoteDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Dto\Response\ActivityStreams\PlaceDto;
use Mitra\Dto\Response\ActivityStreams\ProfileDto;
use Mitra\Dto\Response\ActivityStreams\RelationshipDto;
use Mitra\Dto\Response\ActivityStreams\TombstoneDto;
use Mitra\Dto\Response\ActivityStreams\VideoDto;

final class ActivityStreamTypeToDtoClassMapping
{
    private static $map = [
        // Objects
        'Object' => ObjectDto::class,
        'Article' => ArticleDto::class,
        'Audio' => AudioDto::class,
        'Document' => DocumentDto::class,
        'Event' => EventDto::class,
        'Image' => ImageDto::class,
        'Link' => LinkDto::class,
        'Mention' => MentionDto::class,
        'Note' => NoteDto::class,
        'Place' => PlaceDto::class,
        'Profile' => ProfileDto::class,
        'Relationship' => RelationshipDto::class,
        'Tombstone' => TombstoneDto::class,
        'Video' => VideoDto::class,

        // Actors
        'Person' => PersonDto::class,

        // Activities
        'Create' => CreateDto::class,
        'Follow' => FollowDto::class,
        'Undo' => UndoDto::class,
    ];

    public static function map(string $activityStreamType): string
    {
        if (!array_key_exists($activityStreamType, self::$map)) {
            throw new \RuntimeException(sprintf('Could not map type `%s` to DTO class', $activityStreamType));
        }

        return self::$map[$activityStreamType];
    }

    /**
     * @return array<string, string>
     */
    public static function getMap(): array
    {
        return self::$map;
    }
}
