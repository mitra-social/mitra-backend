<?php

declare(strict_types=1);

namespace Mitra\Mapping\Validation;

use Mitra\Validator\Symfony\Constraint\NotBlank;
use Mitra\Validator\Symfony\ValidationMappingInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class CreateUserRequestDtoValidationMapping implements ValidationMappingInterface
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
                new Length(['min' => 5, 'max' => 32]),
                new Regex('/^[a-z0-9_.-]+$/')
            ])
            ->addPropertyConstraints('email', [
                new Type('string'),
                new Email(),
                new NotNull(),
                new NotBlank(),
            ])
            ->addPropertyConstraints('password', [
                new Type('string'),
                new NotNull(),
                new Length(['min' => 8])
            ])
            ->addPropertyConstraints('displayName', [
                new Type('string'),
                new NotBlank(),
                new Length(['min' => 3])
            ])
        ;
    }
}
