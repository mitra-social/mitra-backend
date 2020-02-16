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
     * SymfonyValidator constructor.
     * @param SymfonyValidatorInterface $validator
     */
    public function __construct(SymfonyValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     */
    public function validate(object $object, array $groups = null): ViolationListInterface
    {
        $violations = [];

        $violationList = $this->validator->validate($object);

        foreach ($violationList as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $violations[] = [
                'path' => $violation->getPropertyPath(),
                'invalidValue' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
            ];
        }

        return new ViolationList($violations);
    }
}
