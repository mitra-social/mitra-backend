<?php

declare(strict_types=1);

namespace Mitra\Tests\Helper\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Psr\Http\Message\ResponseInterface;

final class ResponseStatusCodeConstraint extends Constraint
{

    /**
     * @var integer
     */
    private $expectedStatusCode;

    /**
     * @var string
     */
    private $responseBody;

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
        $statusCode = $other;

        if ($other instanceof ResponseInterface) {
            $statusCode = $other->getStatusCode();
            $this->responseBody = (string) $other->getBody();
        }

        return $this->expectedStatusCode === (int) $statusCode;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString(): string
    {
        return sprintf('is equal to HTTP status code %s (body: %s)', $this->expectedStatusCode, $this->responseBody);
    }
}
