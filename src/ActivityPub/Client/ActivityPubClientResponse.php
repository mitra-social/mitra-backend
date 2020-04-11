<?php

declare(strict_types=1);

namespace Mitra\ActivityPub\Client;

use Mitra\Dto\Response\ActivityStreams\ObjectDto;
use Psr\Http\Message\ResponseInterface;

final class ActivityPubClientResponse
{
    /**
     * @var ResponseInterface
     */
    private $httpResponse;

    /**
     * @var ObjectDto|null
     */
    private $receivedObject;

    public function __construct(ResponseInterface $httpResponse, ?ObjectDto $receivedObject)
    {
        $this->httpResponse = $httpResponse;
        $this->receivedObject = $receivedObject;
    }

    /**
     * @return ResponseInterface
     */
    public function getHttpResponse(): ResponseInterface
    {
        return $this->httpResponse;
    }

    /**
     * @return ObjectDto|null
     */
    public function getReceivedObject(): ?ObjectDto
    {
        return $this->receivedObject;
    }
}
