<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Mitra\Tests\Helper\Constraint\RequestConstraint;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @method MockBuilder getMockBuilder(string $className)
 */
trait ClientMockTrait
{
    /**
     * @param array $requestsAndResponses
     * @return ClientInterface|MockObject
     */
    protected function getClientMock(array $requestsAndResponses): ClientInterface
    {
        $activityPubClientMock = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept()
            ->getMock();

        $requestArguments = [];
        $responseReturnValues = [];

        $requestResponsesCount = count($requestsAndResponses);

        foreach ($requestsAndResponses as $requestAndResponse) {
            list($request, $response) = $requestAndResponse;

            /** @var RequestInterface $request */

            $requestArguments[] = [new RequestConstraint($request)];

            $responseReturnValues[] = $response;
        }

        $activityPubClientMock->expects(self::exactly($requestResponsesCount))->method('sendRequest')
            ->withConsecutive(...$requestArguments)->willReturnOnConsecutiveCalls(...$responseReturnValues);

        return $activityPubClientMock;
    }
}
