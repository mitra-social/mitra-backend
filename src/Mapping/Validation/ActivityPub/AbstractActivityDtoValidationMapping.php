<?php

declare(strict_types=1);

namespace Mitra\Mapping\Validation\ActivityPub;

use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class AbstractActivityDtoValidationMapping extends ObjectDtoValidationMapping
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     */
    public function configureMapping(ClassMetadata $metadata): void
    {
        parent::configureMapping($metadata);

        $metadata
            ->addPropertyConstraints('actor', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('target', self::getMultipleObjectOrLinkConstraints())
            ->addPropertyConstraints('result', self::getObjectOrLinkConstraints())
            ->addPropertyConstraints('origin', self::getObjectOrLinkConstraints())
            ->addPropertyConstraints('instrument', self::getMultipleObjectOrLinkConstraints())
        ;
    }
}
