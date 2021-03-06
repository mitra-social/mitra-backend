<?php

declare(strict_types=1);

namespace Mitra\Mapping\Validation\ActivityPub;

use Mitra\Dto\Response\ActivityStreams\CollectionDto;
use Mitra\Dto\Response\ActivityStreams\ImageDto;
use Mitra\Dto\Response\ActivityStreams\LinkDto;
use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Mitra\Mapping\Dto\ActivityStreamTypeToDtoClassMapping;
use Mitra\Validator\Symfony\Constraint\AllIfArray;
use Mitra\Validator\Symfony\Constraint\NotBlank;
use Mitra\Validator\Symfony\Constraint\Valid;
use Mitra\Validator\Symfony\ValidationMappingInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ObjectDtoValidationMapping implements ValidationMappingInterface
{
    private const DATETIME_FORMAT = \DateTime::RFC3339;

    public function configureMapping(ClassMetadata $metadata): void
    {
        $imageOrLinkConstraints = [
            new Type(['array', 'string', ImageDto::class, LinkDto::class]),
            new Valid(),
            new AllIfArray([
                new Type(['string', ImageDto::class, LinkDto::class]),
                new Valid(),
            ]),
        ];

        $langStringConstraints = [
            new Type('array'),
            new All(
                new Type('string'),
            ),
        ];

        $metadata
            ->addPropertyConstraints('context', [
                new Type(['array', 'string']),
                new NotBlank(),
            ])
            ->addPropertyConstraints('id', [
                new Type('string'),
                new NotBlank(),
            ])
            ->addPropertyConstraints('type', [
                new Type('string'),
                new NotBlank(),
                new NotNull(),
                new Choice([
                    'choices' => array_keys(ActivityStreamTypeToDtoClassMapping::getMap()),
                    'message' => 'Type {{ value }} is not a valid ActivityPub type.'
                ]),
            ])
            ->addPropertyConstraints('attachment', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('attributedTo', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('audience', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('content', [
                new Type('string'),
            ])
            ->addPropertyConstraints('contentMap', $langStringConstraints)
            ->addPropertyConstraints('name', [
                new Type('string'),
            ])
            ->addPropertyConstraints('nameMap', $langStringConstraints)
            ->addPropertyConstraints('endTime', [
                new Type('string'),
                new DateTime(self::DATETIME_FORMAT),
            ])
            ->addPropertyConstraints('generator', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('icon', $imageOrLinkConstraints)
            ->addPropertyConstraints('image', $imageOrLinkConstraints)
            ->addPropertyConstraints('inReplyTo', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('location', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('preview', self::getObjectOrLinkConstraints())
            ->addPropertyConstraints('published', [
                new Type('string'),
                new DateTime(self::DATETIME_FORMAT),
            ])
            ->addPropertyConstraints('replies', [
                new Type(CollectionDto::class),
                new Valid(),
            ])
            ->addPropertyConstraints('startTime', [
                new Type('string'),
                new DateTime(self::DATETIME_FORMAT),
            ])
            ->addPropertyConstraints('summary', [
                new Type('string'),
            ])
            ->addPropertyConstraints('summaryMap', $langStringConstraints)
            ->addPropertyConstraints('tag', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('updated', [
                new Type('string'),
                new DateTime(self::DATETIME_FORMAT),
            ])
            ->addPropertyConstraints('url', self::getMultipleLinkConstraints())
            ->addPropertyConstraints('to', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('bto', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('cc', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('bcc', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('mediaType', [
                new Type('string'),
                new Regex('~^[-\w.]+/[-+\w.]+$~'),
            ])
            ->addPropertyConstraints('duration', [
                new Type('string'),
                // P[JY][MM][WW][TD][T[hH][mM][s[.f]S]]
                new Regex('/^P(?!$)(\d+Y)?(\d+M)?(\d+W)?(\d+D)?(T(?=\d)(\d+H)?(\d+M)?(\d+(\.\d+)?S)?)?$/'),
            ])
        ;
    }

    /**
     * @return array<Constraint>
     */
    protected static function getObjectOrLinkConstraints(): array
    {
        return [
            new Type(['string', ObjectDto::class, LinkDto::class]),
            new Valid(),
        ];
    }

    /**
     * @return array<Constraint>
     */
    protected static function getMultipleObjectOrLinkConstraints(): array
    {
        return [
            new Type(['array', 'string', ObjectDto::class, LinkDto::class]),
            new Valid(),
            new AllIfArray([
                new Type(['string', ObjectDto::class, LinkDto::class]),
                new Valid(),
            ]),
        ];
    }

    /**
     * @return array<Constraint>
     */
    protected static function getMultipleLinkConstraints(): array
    {
        return [
            new Type(['array', 'string', LinkDto::class]),
            new Valid(),
            new AllIfArray([
                new Type(['string', LinkDto::class]),
                new Valid(),
            ]),
        ];
    }
}
