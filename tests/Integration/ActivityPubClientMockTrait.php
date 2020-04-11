<?php

declare(strict_types=1);

namespace Mitra\Tests\Integration;

use Mitra\ActivityPub\Client\ActivityPubClient;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @method MockBuilder getMockBuilder(string $className)
 */
trait ActivityPubClientMockTrait
{
    /**
     * @param array $requestsAndResponses
     * @return ActivityPubClient|MockObject
     */
    protected function getActivityPubClientMock(array $requestsAndResponses): ActivityPubClient
    {
        $activityPubClientMock = $this->getMockBuilder(ActivityPubClient::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept()
            ->getMock();

        $createRequestParams = [];
        $requestReturnValues = [];
        $requestArguments = [];
        $responseReturnValues = [];

        $requestResponsesCount = count($requestsAndResponses);

        foreach ($requestsAndResponses as $requestAndResponse) {
            list($request, $response, $objectParameter) = $requestAndResponse;

            $createRequestParams[] = [
                $request->getMethod(), (string) $request->getUri(), $objectParameter
            ];
            $requestReturnValues[] = $request;
            $requestArguments[] = [$request];
            $responseReturnValues[] = $response;
        }

        $activityPubClientMock->expects(self::exactly($requestResponsesCount))->method('createRequest')
            ->withConsecutive(...$createRequestParams)
            ->willReturnOnConsecutiveCalls(...$requestReturnValues);

        $activityPubClientMock->expects(self::exactly($requestResponsesCount))->method('sendRequest')
            ->withConsecutive(...$requestArguments)->willReturnOnConsecutiveCalls(...$responseReturnValues);

        return $activityPubClientMock;
    }
}
