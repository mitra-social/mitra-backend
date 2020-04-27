<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ResponseStatusCodeConstraint extends Constraint
{

    /**
     * @var integer
     */
    private $expectedStatusCode;

    /**
     * @param integer $expectedStatusCode
     */
    public function __construct(int $expectedStatusCode)
    {
        $this->expectedStatusCode = $expectedStatusCode;
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other): bool
    {
        if (!$other instanceof ResponseInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected value of type `%s`, `%s` given',
                ResponseInterface::class,
                is_object($other) ? get_class($other) : gettype($other)
            ));
        }

        $statusCode = $other->getStatusCode();

        return $this->expectedStatusCode === (int) $statusCode;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return sprintf('is equal to expected HTTP status code %s', $this->expectedStatusCode);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * To provide additional failure information additionalFailureDescription
     * can be used.
     *
     * @param mixed $other evaluated value or object
     * @return string
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        if (!$other instanceof ResponseInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected value of type `%s`, `%s` given',
                ResponseInterface::class,
                is_object($other) ? get_class($other) : gettype($other)
            ));
        }

        $actualBody = (string) $other->getBody();

        return sprintf(
            'actual HTTP status code %s (body: %s)',
            $other->getStatusCode(),
            '' !== $actualBody ? $actualBody : '<empty>'
        ) . ' ' . $this->toString();
    }
}
