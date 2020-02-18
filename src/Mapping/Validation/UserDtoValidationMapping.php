<?php

declare(strict_types=1);

namespace Mitra\Mapping\Validation;

use Mitra\Dto\NestedDto;
use Mitra\Validator\Symfony\Constraint\NotBlank;
use Mitra\Validator\Symfony\Constraint\Valid;
use Mitra\Validator\Symfony\ValidationMappingInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserDtoValidationMapping implements ValidationMappingInterface
{

    /**
     * @param ClassMetadata $metadata
     * @return void
     */
    public function configureMapping(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraints('preferredUsername', [
                new Type('string'),
                new NotNull(),
                new NotBlank(),
                new Length(['min' => 5, 'max' => 32]),
            ])
            ->addPropertyConstraints('email', [
                new Type('string'),
                new Email(),
                new NotBlank(),
            ])
            ->addPropertyConstraints('nested', [
                new Type(NestedDto::class),
                new NotNull(),
                new Valid(),
            ]);
    }
}
