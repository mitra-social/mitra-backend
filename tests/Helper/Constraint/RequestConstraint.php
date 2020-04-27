<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\RequestInterface;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class RequestConstraint extends Constraint
{

    /**
     * @var RequestInterface
     */
    private $expectedRequest;

    public function __construct(RequestInterface $expectedRequest)
    {
        $this->expectedRequest = $expectedRequest;
    }

    /**
     * @param mixed $other
     * @return boolean
     */
    protected function matches($other): bool
    {
        if (!$other instanceof RequestInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected value of type `%s`, `%s` given',
                RequestInterface::class,
                is_object($other) ? get_class($other) : gettype($other)
            ));
        }

        foreach ($this->expectedRequest->getHeaders() as $name => $values) {
            if ($other->getHeaderLine($name) !== $this->expectedRequest->getHeaderLine($name)) {
                return false;
            }
        }

        return $this->expectedRequest->getMethod() === $other->getMethod()
            && (string) $this->expectedRequest->getUri() === (string) $other->getUri()
            && (string) $this->expectedRequest->getBody() === (string) $other->getBody();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return sprintf('is equal to expected request %s', $this->requestToString($this->expectedRequest));
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
        if (!$other instanceof RequestInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected value of type `%s`, `%s` given',
                RequestInterface::class,
                is_object($other) ? get_class($other) : gettype($other)
            ));
        }

        return sprintf('actual request %s', $this->requestToString($other)) . ' ' . $this->toString();
    }

    private function requestToString(RequestInterface $request): string
    {
        $body = (string) $request->getBody();

        $headersAsString = [];

        foreach ($this->expectedRequest->getHeaders() as $name => $values) {
            $headersAsString[] = $name . ': ' . $request->getHeaderLine($name) ?? '<missing>';
        }

        return sprintf(
            '%s %s (headers=%s, body=%s)',
            $request->getMethod(),
            (string) $request->getUri(),
            implode(', ', $headersAsString),
            '' !== $body ? $body : '<empty>'
        );
    }
}
