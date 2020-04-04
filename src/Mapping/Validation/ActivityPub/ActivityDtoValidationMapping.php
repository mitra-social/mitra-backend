<?php

declare(strict_types=1);

namespace Mitra\Mapping\Validation\ActivityPub;

use Symfony\Component\Validator\Mapping\ClassMetadata;

final class ActivityDtoValidationMapping extends AbstractActivityDtoValidationMapping
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        parent::configureMapping($metadata);

        $metadata
            ->addPropertyConstraints('object', self::getMultipleObjectOrLinkConstraints())
        ;
    }
}
