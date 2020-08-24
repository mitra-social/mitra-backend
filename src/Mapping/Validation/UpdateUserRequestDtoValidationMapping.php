<?php

declare(strict_types=1);

namespace Mitra\Mapping\Validation;

use Mitra\Validator\Symfony\Constraint\NotBlank;
use Mitra\Validator\Symfony\ValidationMappingInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class UpdateUserRequestDtoValidationMapping implements ValidationMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraints('email', [
                new Type('string'),
                new Email(),
                new NotBlank(),
            ])
            ->addPropertyConstraints('newPassword', [
                new Type('string'),
                new NotBlank(),
                new Length(['min' => 8])
            ])
            ->addPropertyConstraints('password', [
                new Type('string'),
                new NotNull(),
                new NotBlank(),
                new Length(['min' => 3])
            ])
        ;
    }
}
