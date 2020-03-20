<?php

declare(strict_types=1);

namespace Mitra\Validator;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface as SymfonyValidatorInterface;

final class SymfonyValidator implements ValidatorInterface
{

    /**
     * @var SymfonyValidatorInterface
     */
    private $validator;

    /**
     * @param SymfonyValidatorInterface $validator
     */
    public function __construct(SymfonyValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param object $object
     * @param array<string>|null $groups
     * @return ViolationListInterface
     */
    public function validate(object $object, array $groups = null): ViolationListInterface
    {
        $violations = [];

        $violationList = $this->validator->validate($object);

        foreach ($violationList as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $violations[] = new Violation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getRoot(),
                $violation->getPropertyPath(),
                $violation->getInvalidValue()
            );
        }

        return new ViolationList($violations);
    }
}
