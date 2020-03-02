<?php

declare(strict_types=1);

namespace Mitra\Mapping\Validation;

use Mitra\Validator\Symfony\Constraint\NotBlank;
use Mitra\Validator\Symfony\ValidationMappingInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class TokenRequestDtoValidationMapping implements ValidationMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraints('username', [
                new Type('string'),
                new NotNull(),
                new NotBlank(),
            ])
            ->addPropertyConstraints('password', [
                new Type('string'),
                new NotNull(),
                new NotBlank(),
            ])
        ;
    }
}
